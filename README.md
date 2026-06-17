# File Upload Audit

Módulo custom para **Drupal 10 / Drupal 11** que permite auditar los archivos cargados en el sitio y visualizar qué usuario los subió.

El módulo agrega un reporte administrativo que lista los registros de la tabla `file_managed` y los cruza con `users_field_data` para mostrar información útil sobre cada archivo.

---

## Características

* Lista todos los archivos registrados en Drupal.
* Muestra el usuario que cargó cada archivo.
* Muestra información técnica del archivo:

  * FID
  * Nombre del archivo
  * URI
  * Tipo MIME
  * Peso
  * UID del usuario
  * Nombre de usuario
  * Fecha de carga
  * Fecha de modificación
  * Estado del archivo
* Agrega una pantalla dentro de los reportes administrativos de Drupal.
* Incluye permiso propio para controlar qué roles pueden acceder al reporte.

---

## Ruta del reporte

Una vez instalado, el reporte queda disponible en:

```txt
/admin/reports/file-upload-audit
```

También se puede acceder desde:

```txt
Administración → Reportes → Auditoría de archivos subidos
```

---

## Requisitos

* Drupal `^10` o `^11`
* Composer
* Drush recomendado para instalación y limpieza de caché

---

## Instalación con Composer desde GitHub

Desde la raíz del proyecto Drupal, agregar el repositorio:

```bash
composer config repositories.file_upload_audit vcs https://github.com/rsampaoli/file_upload_audit.git
```

Luego instalar el módulo:

```bash
composer require rsampaoli/file-upload-audit:^1.0
```

Activar el módulo con Drush:

```bash
vendor/bin/drush en file_upload_audit -y
vendor/bin/drush cr
```

---

## Instalación usando DDEV

Si el proyecto usa DDEV:

```bash
ddev composer config repositories.file_upload_audit vcs https://github.com/rsampaoli/file_upload_audit.git
ddev composer require rsampaoli/file-upload-audit:^1.0
ddev drush en file_upload_audit -y
ddev drush cr
```

---

## Instalación en desarrollo

Para instalar directamente desde la rama `main`:

```bash
composer config repositories.file_upload_audit vcs https://github.com/rsampaoli/file_upload_audit.git
composer require rsampaoli/file-upload-audit:dev-main
vendor/bin/drush en file_upload_audit -y
vendor/bin/drush cr
```

---

## Permisos

El módulo agrega el siguiente permiso:

```txt
View file upload audit
```

Para habilitarlo:

1. Ir a `/admin/people/permissions`
2. Buscar `View file upload audit`
3. Asignarlo al rol correspondiente
4. Guardar los permisos

---

## ¿Qué datos muestra?

El reporte toma la información principal desde la tabla:

```txt
file_managed
```

Y la cruza con:

```txt
users_field_data
```

Relación utilizada:

```sql
file_managed.uid = users_field_data.uid
```

De esta forma, el módulo puede mostrar qué usuario cargó cada archivo registrado en Drupal.

---

## Importante

Este módulo lista archivos registrados por Drupal en `file_managed`.

Por ejemplo:

```txt
public://2026-02/PUERTO.svg
```

o archivos subidos desde campos de imagen, campos de archivo, Media Library, formularios o gestores de contenido.

No lista archivos que forman parte del código fuente del proyecto, como imágenes incluidas dentro de un theme o módulo custom:

```txt
web/themes/custom/mi_theme/images/logo.svg
web/modules/custom/mi_modulo/assets/footer.png
```

Esos archivos no tienen un registro en `file_managed`, por lo tanto Drupal no guarda un usuario asociado a ellos.

---

## Actualización del módulo

Para actualizar el módulo cuando haya una nueva versión/tag:

```bash
composer update rsampaoli/file-upload-audit
vendor/bin/drush cr
```

Con DDEV:

```bash
ddev composer update rsampaoli/file-upload-audit
ddev drush cr
```

---

## Desinstalación

Para desinstalar el módulo:

```bash
vendor/bin/drush pmu file_upload_audit -y
composer remove rsampaoli/file-upload-audit
vendor/bin/drush cr
```

Con DDEV:

```bash
ddev drush pmu file_upload_audit -y
ddev composer remove rsampaoli/file-upload-audit
ddev drush cr
```

---

## Estructura del módulo

```txt
file_upload_audit/
├── composer.json
├── file_upload_audit.info.yml
├── file_upload_audit.routing.yml
├── file_upload_audit.permissions.yml
├── file_upload_audit.links.menu.yml
└── src/
    └── Controller/
        └── FileUploadAuditController.php
```

---

## Autor

Desarrollado por [Ramiro Sampaoli](https://github.com/rsampaoli).

---

## Licencia

MIT
