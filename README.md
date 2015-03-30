yiiframework.com Website
========================

This project contains the source code for the yiiframework.com Website.


## INSTALLATION

Before you start, make sure you have installed [composer](https://getcomposer.org/) and [Node.js](http://nodejs.org/).
 
```
# clone the project
git clone git@github.com:qiangxue/yiiframework.com.git

cd yiiframework.com

# install the composer asset plugin globally, if you haven't done so before
composer global require "fxp/composer-asset-plugin:1.0.0"

# install the dependent composer packages
composer install

# install grunt-cli globally if you haven't done so before
npm install -g grunt-cli

# install dependent NPM modules
npm install

# initialize the application, choose "development"
./init

# clone yii repositories for generating API and guide documentation (yii 1.1 also needs dependencies for this)
git clone git@github.com:yiisoft/yii.git data/yii-1.0
cd data/yii-1.0
git checkout 1.0.9
cd ../..
git clone git@github.com:yiisoft/yii.git data/yii-1.1
cd data/yii-1.1
composer install --prefer-dist
cd ../..
git clone git@github.com:yiisoft/yii2.git data/yii-2.0

# build guide and API documentation (will be put in cron job on production)
./yii api 2.0 --interactive=0
./yii guide 2.0 --interactive=0
./yii api 1.1 --interactive=0
./yii guide 1.1 --interactive=0
./yii api 1.0 --interactive=0
./yii guide 1.0 --interactive=0

# build js/css files
grunt build
```


### Web Server Setup

Define a host name `local.yiiframework.com` that points to `localhost`.

Define a virtual host `local.yiiframework.com` which uses `yiiframework.com/web` as its document root.

If you are using Apache, add the following configuration for the `yiiframework.com/web` folder to hide the
entry script:

```
RewriteEngine on

# if a directory or a file exists, use it directly
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# otherwise forward it to index.php
RewriteRule . index.php
```


## DIRECTORY STRUCTURE

      commands/           contains console commands (controllers)
      config/             contains application configurations
      controllers/        contains Web controller classes
      data/               contains important data generated by different commands
      env/                contains environment-dependent files
      less/               contains LESS source files
      mail/               contains view files for e-mails
      models/             contains model classes
      node_modules/       contains installed NPM packages
      runtime/            contains files generated during runtime
      js/                 contains JS source files
      scripts/            contains shell scripts
      vendor/             contains dependent 3rd-party packages
      views/              contains view files for the Web application
      web/                contains the entry script and Web resources


## DEVELOPMENT

### Build

* During development, run `grunt` to watch LESS/JS file changes and automatically build target CSS/JS files.
* At any time, run `grunt build` to manually rebuild target CSS/JS files from source LESS/JS files. 


### CSS Files

* Use LESS files to define CSS styles. 
* All LESS files should be put under `/less` and listed in `/less/all.less`.
* Usually each controller corresponds to a single LESS file whose name is the same as the controller ID.
  For example, the `GuideController` has a LESS file named `guide.less`.
  

### JS Files

* All JS files should be put under `/js` and listed in `/js/all.json`.
* Usually each controller corresponds to a single JS file whose name is the same as the controller ID.
  For example, the `GuideController` has a LESS file named `guide.js`.