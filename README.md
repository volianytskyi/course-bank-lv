# Ministra (Stalker Portal) Latvijas Banka Exchange Rates Parser

The provided method uses course.nbu module to show the Latvijas Banka exchange rates

### Intergation
* Put course.class.php and banklvxmldata.class.php files in stalker_portal/server/lib/ directory (replace the existing course.class.php with the new one)
* Set these variables in [custom.ini](https://wiki.infomir.eu/eng/ministra-tv-platform/ministra-installation-guide/configuration-file):

```
all_modules[] = course.nbu
exchange_rate_classes[] = Course
```
