Deployee plugin for OXID eShop
==============================
# Dependencies
## OXID eShop console
This plugin makes use of nexusnetsoft/oxid-eshop-console. For this reason it has to be implemented in your shop.

## Deployee plugin DeployShell
To execute the OXID eShop console the Shell task from Deployee plugin DeployShell is being used.

# Installation
This plugin is disabled by default. To enable it you just need to add the following configuration to your config file
```bash
plugins:
    - Deployee\Plugins\DeployOxid\DeployOxidPlugin:
        enabled: true
```

# New tasks
## Shop module task
This task can be used for activating or deactivating one module.

### Creating new module task
```php
$this
    ->oxidModule("moduleid");
```
The example above will create a new module task. After this you are required to configure al least wether the module should be activated or deactivated.

### Activating a module
```php
$this
    ->oxidModule("moduleid")
    ->activate();
```
To activate the module just call the tasks activate method.

### Deactivating a module
```php
$this
    ->oxidModule("moduleid")
    ->deactivate();
```
To deactivate the module you would call the tasks deactivate method.

### Limiting task to specified shop instances
```php
$this
    ->oxidModule("moduleid")
    ->activate()
    ->forShopId(2)
    ->forShopId(5);
```
You can use the tasks forShopId method to add an id of a shop instance to apply the activation or deactivation to.

## Shop cnfig tasks
### Adding or modify a databse shop config
```php
$this->oxidShopConfig($shopId, $varName, $varValue, $varType, $module);
```
The basic usage is as shown above.

A real example could be like
```php
$this->oxidShopConfig(1, "blShowTags", false, "bool");
$this->oxidShopConfig(1, "aDetailImageSizes", [
  'oxpic1' => '579*579',
  'oxpic2' => '579*579',
  'oxpic3' => '579*579',
  'oxpic4' => '579*579',
  'oxpic5' => '579*579',
  'oxpic6' => '579*579',
  'oxpic7' => '579*579',
  'oxpic8' => '579*579',
  'oxpic9' => '579*579',
  'oxpic10' => '579*579',
  'oxpic11' => '579*579',
  'oxpic12' => '579*579'
], "aarr", "theme:azure");
$this->oxidShopConfig(1, "aIpWhitelist", ["127.0.0.1", "192.168.1.1"], "arr", "module:phz_ipblock");
```
The example above shows how to set general or module and theme specific configuration.

## Common shop tasks
This task povides common tasks to execute in the shop instance.

### Creating new shop task
```php
$this
    ->oxidShop();
```
This example will add a new shop task. You may configure the task as followed.

### Clear tmp directory
```php
$this
    ->oxidShop()
    ->clearTmp();
```
This task will recursively remove all files from the shops configured sCompileDir, except the .htaccess file.

### Generate database views
```php
$this
    ->oxidShop()
    ->generateViews();
```
This task will execute the shops view generation for all shop instances.

### Create admin user
```php
$this
    ->oxidShop()
    ->createAdminUser('shopadmin', 'myawesomepassword');
```
Creates a new malladmin user for backend usage