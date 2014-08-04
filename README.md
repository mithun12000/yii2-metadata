yii2-metadata
=============

Meta data provider for Yii2 created from https://github.com/mdmsoft/yii2-admin/blob/master/components/AccessHelper.php


Installation
=======

Using ```composer```

```
"require": {
	...other dependency...	
	"mithun12000/yii2-metadata":"*"
},
```

Add as extension. Code:

```php

'metadata' => 
  [
    'name' => 'metadata',
    'version' => '1.0',
    'alias' => 
    [
      '@yii/metadata/' => [EXTENSION_PATH] '/yii2-metadata',
    ],
  ],
  
```
