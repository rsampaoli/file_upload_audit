<?php

namespace Drupal\file_upload_audit\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\PagerSelectExtender;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller para el reporte de auditoría de archivos subidos.
 */
class FileUploadAuditController extends ControllerBase
{

    /**
     * Conexión a la base de datos.
     *
     * @var \Drupal\Core\Database\Connection
     */
    protected Connection $database;

    /**
     * Servicio para formatear fechas.
     *
     * @var \Drupal\Core\Datetime\DateFormatterInterface
     */
    protected DateFormatterInterface $dateFormatter;

    /**
     * Servicio para generar URLs de archivos.
     *
     * @var \Drupal\Core\File\FileUrlGeneratorInterface
     */
    protected FileUrlGeneratorInterface $fileUrlGenerator;

    /**
     * Constructor.
     */
    public function __construct(
        Connection $database,
        DateFormatterInterface $date_formatter,
        FileUrlGeneratorInterface $file_url_generator,
    ) {
        $this->database = $database;
        $this->dateFormatter = $date_formatter;
        $this->fileUrlGenerator = $file_url_generator;
    }

    /**
     * Inyección de dependencias.
     */
    public static function create(ContainerInterface $container): static
    {
        return new static(
            $container->get('database'),
            $container->get('date.formatter'),
            $container->get('file_url_generator')
        );
    }

    /**
     * Reporte de archivos subidos.
     */
    public function report(): array
    {
        $header = [
            'fid' => $this->t('FID'),
            'filename' => $this->t('Archivo'),
            'uri' => $this->t('URI'),
            'filemime' => $this->t('Tipo'),
            'filesize' => $this->t('Peso'),
            'user' => $this->t('Usuario que lo subió'),
            'uid' => $this->t('UID'),
            'created' => $this->t('Fecha de carga'),
            'changed' => $this->t('Última modificación'),
            'status' => $this->t('Estado'),
        ];

        $query = $this->database->select('file_managed', 'f')
            ->extend(PagerSelectExtender::class);

        $query->fields('f', [
            'fid',
            'uid',
            'filename',
            'uri',
            'filemime',
            'filesize',
            'status',
            'created',
            'changed',
        ]);

        $query->leftJoin('users_field_data', 'u', 'u.uid = f.uid');
        $query->addField('u', 'name', 'username');
        $query->addField('u', 'mail', 'user_mail');

        $query->orderBy('f.created', 'DESC');
        $query->limit(50);

        $results = $query->execute();

        $rows = [];

        foreach ($results as $record) {
            $file_link = $record->filename;

            try {
                $file_url = $this->fileUrlGenerator->generate($record->uri);
                $file_link = Link::fromTextAndUrl($record->filename, $file_url)->toString();
            } catch (\Throwable $e) {
                // Si por algún motivo no se puede generar la URL, mostramos solo el nombre.
                $file_link = $record->filename;
            }

            if (!empty($record->uid) && !empty($record->username)) {
                $user_label = Link::createFromRoute(
                    $record->username,
                    'entity.user.canonical',
                    ['user' => $record->uid]
                )->toString();
            } elseif ((int) $record->uid === 0) {
                $user_label = $this->t('Anónimo');
            } else {
                $user_label = $this->t('Usuario no encontrado');
            }

            $rows[] = [
                'fid' => $record->fid,
                'filename' => $file_link,
                'uri' => $record->uri,
                'filemime' => $record->filemime,
                'filesize' => $this->formatBytes((int) $record->filesize),
                'user' => $user_label,
                'uid' => $record->uid,
                'created' => $this->dateFormatter->format((int) $record->created, 'custom', 'Y-m-d H:i:s'),
                'changed' => $this->dateFormatter->format((int) $record->changed, 'custom', 'Y-m-d H:i:s'),
                'status' => ((int) $record->status === 1) ? $this->t('Permanente') : $this->t('Temporal'),
            ];
        }

        return [
            'intro' => [
                '#markup' => '<p>Este reporte lista los archivos registrados en <code>file_managed</code> y cruza el <code>uid</code> con <code>users_field_data</code> para mostrar qué usuario los subió.</p>',
            ],
            'table' => [
                '#type' => 'table',
                '#header' => $header,
                '#rows' => $rows,
                '#empty' => $this->t('No se encontraron archivos.'),
            ],
            'pager' => [
                '#type' => 'pager',
            ],
            '#attached' => [
                'library' => [
                    'core/drupal.tableselect',
                ],
            ],
        ];
    }

    /**
     * Formatea bytes a KB, MB, GB, etc.
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return round($bytes / (1024 ** $power), 2) . ' ' . $units[$power];
    }
}