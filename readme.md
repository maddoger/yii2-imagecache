Yii2 ImageCache by maddoger

#Component:

```
'imageCache' => [
    'class' => 'maddoger\imagecache\ImageCache',
    'generateWithUrl' => false,
    'actionSavesFile' => false,

    //Avatar
    'presets' => [
        '100x100' => [
            'fit' => [
                'width' => 100,
                'height' => 100,
            ],
        ],
        '200x' => [
            'thumbnail' => [
                'width' => 200,
                'height' => 200,
            ],
        ],
    ],
],
```

#For server generation

##In controller:
```
public function actions()
    {
        return [
            'imagecache' => [
                'class' => 'maddoger\imagecache\ImageAction',
            ],
        ];
    }
```

##In configuration:

```
'urlManager' => [
    ...
    'rules' => [
        ...
        'static/ic/<img:.*?>' => 'site/imagecache',
        ...
    ],
    ...
],
```

##.htaccess

```
RewriteEngine On

RedirectMatch 403 /\.
RedirectMatch 403 /\.htaccess$

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} ^/static/ic/*
RewriteRule ^(.*)$  ../index.php [QSA,L]
```

#Faster generation

##Standalone php script

Use files from server folder.

For faster generation you may use file in server folder (.htaccess and generator script), or another methods.