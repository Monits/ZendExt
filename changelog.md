# Changelog

## 1.6 (under development)
 > ZendExt is now dual-licensed under both Apache 2 and New BSD licenses

 - **(Improvement)** Move `ZendExt_Service_Facebook` to use the new graph API instead of the old REST api. Currently using version 3.2.0. This change includes the addition of several new methods and features for this service.
 - **(Fix)** Builder validator now properly uses `ZendExt_Validate_Boolean` instead of the non-existing `Zend_Validate_Boolean`
 - **(Fix)** Fix indentation of constructors created with the DAO generator.
 - **(Improvement)** Make the output of the code generator more verbose.
 - **(Fix)** Allow generators and `ZendExt_Db_Schema` to work with PostgreSQL.
 - **(Fix)** Allow repeated calls to `ZendExt_Db_Dao_Select::assemble` (this fixes an issue where the paginator would break if the query had no `from` explicitly set).
 - **(Improvement)** Use our open source super poms for better management.
 - **(Improvement)** Crons can now specify the environment in which to run through config.
 - **(Improvement)** Added a generator for DAOs.
 - **(Fix)** The builder generator now properly quotes default values which are strings.
 - **(Improvement)** Added service for Apple Push Notification Service (APNS)
 - **(Fix)** Several checkstyle warnings.
 - **(Improvement)** `ZendExt_Controller_CRUDAbstract` allows to create and add another from the creation form.
 - **(Fix)** Case errors in `ZendExt_Controller_CRUDAbstract`
 - **(Fix)** Db errors in `ZendExt_Controller_CRUDAbstract` are no longer assumed to be _"Duplicate entry"_
 - **(Improvement)** Crons do no longer rely on the mere existance of a pidfile to consider a process as running, but actually checks the process is running (this relies on PCNTL).
 - **(Fix)** Incorrect javadoc on generated models and fix param names in cases where it didn't match the column name.
 - **(Improvement)** Added `phpunit.xml` for proper running of tests in all environments.
 - **(Improvement)** Added a `ZendExt_Version` based on `Zend_Version` for the same purposes.
 - **(Fix)** When a child process of a cron ends, it's now properly cleared from the parent's map of childs.
 - **(Improvement)** Crons can now wait for _any_ child to finish (useful when you want to cap the number of child processes).
 - **(Improvement)** Added a set of `_queryFor*Shards` methods to `ZendExt_Db_Dao_Abstract` to allow executing custom queries that may not fit in a `ZendExt_Db_Dao_Select` of the `_insert`, `_update` and `_delete` methods.
 - **(Improvement)** Allow to download third party dependencies (such as Facebook's SDK) from Monits' repositories.
 - **(Fix)** Builder generator was setting an invalid format for date validation.
 - **(Fix)** Builder generator was setting an invalid format for default `CURRENT_DATE` values.
 - **(Improvement)** Cron crashes are logged along the exception for easier debugging.
 - **(Fix)** Table generator help now properly shows required arguments.
 - **(Fix)** The form used for updates and creates in `ZendExt_Controller_CRUDAbstract` was passed under different names to the templates. Now both are called the same (_"form"_) for consistency.
 - **(Fix)** Joins in `ZendExt_Db_Dao_Abstract` were not applied unless a call to `from`was also performed.
 - **(Fix)** The generator now honors the output directory argument to output created files.
 - **(Fix)** Let the Table generator respect the table's name casing so it will allways match.

## 1.5
 - **(Change)** `ZendExt_Dao_Abstract` is now `ZendExt_Db_Dao_Abstract` for compatibility with Zend.
 - **(Fix)** running `mvn site` now longer breaks.
 - **(Improvement)** Fields with `null` values are automatically shown as disabled in the update forms of `ZendExt_Controller_CRUDAbstrsact`
 - **(Fix)** Better documentation for `ZendExt_Service_Mailing::send` showing multi-send alternatives.
 - **(Improvement)** `ZendExt_Controller_CRUDAbstract` now allows to set custom labels and titles. Still needs work to get integrated with I18N.
 - **(Fix)** Prevent warning in builders with non-set optional arguments.
 - **(Improvement)** `ZendExt_Controller_CRUDAbstract` now allows to set nice display names for fields instead of column names in all views.
 - **(Improvement)** Better error message for `ZendExt_Validate_Uri`.
 - **(Fix)** Generated tables have their primary field properly quoted.
 - **(Improvement)** Added services to support DineroMail's APIs.
 - **(Improvement)** Added a helper to create a url from an object, usefull for REST.
 - **(Improvement)** Created `ZendExt_Validate_Boolean` to validate booleans (`true` and `false`, no casts).
 - **(Improvement)** Added a `ZendExt_Db_Dao_Select` to generate queries in DAOs with the same interface as provided by `Zend_Db_Select`.
 - **(Improvement)** Added a `ZendExt_Paginator_Adapter_DbDaoSelect` to paginate queries created with `ZendExt_Db_Dao_Select`.
 - **(Improvement)** Completely refactored `ZendExt_Db_Dao_Abstract`. Much cleaner code, and more functionality, for instance, there are `_update`, `_delete` and `_paginate` methods now.
 - **(Improvement)** `ZendExt_Db_Dao_Abstract` now uses hydrators to generate objects from the raw db data. An interface and default implementation are provided.
 - **(Improvement)** Refactored `ZendExt_Controller_CRUDAbstract` to allow it wo work with both, `Zend_Db_Table` and `ZendExt_Db_Dao_Abstract`.
 - **(Improvement)** If no configuration was done, `ZendExt_Db_Dao_Abstract` defaults to using the default adapter.
 - **(Improvement)** Cleaned up the code for `ZendExt_Application_Resource_Multidb`.
 - **(Fix)** Builder generator didn't respect our coding standards.

## 1.4
 - **(Improvement)** `ZendExt_Service_Facebook` now allows to publish to the user's wall.

## 1.3
 - **(Fix)** `LazyStream` loggers are now writting to the proper folder when used from crons.
 - **(Fix)** Fixed issues with nullable columns with defaults in builder generator.
 - **(Fix)** Fixed issues with underscores in the columns names in the builder generator.
 - **(Fix)** Checkstyle warnings.
 - **(Improvement)** Added `ZendExt_View_Helper_TimeElapsed` to output nice messages for elapsed time since an action (typical _"1 month ago"_ / _"3 minutes ago"_ messages).
 - **(Improvement)** Verify the SSL certificate for MercadoPago's Sonda service.
 - **(Improvement)** Refactored select methods in `ZendExt_Dao_Abstract` for easier use and better perfomance. Queries are no longer executed on every single shard, but **only** to those that expect to be affected.
 - **(Improvement)** Added `ZendExt_Validate_Uri` to validate URIs.
 - **(Improvement)** Added `ZendExt_Service_MercadoPago` to inetgrate MercadoPago's APIs for purchasing goods and services.
 - **(Fix)** Prevent _"MySQL has gone away"_ errors in `ZendExt_Dao_Abstract`.
 - **(Improvement)** `ZendExt_Controller_CRUDAbstract` now displays db errors.
 - **(Improvement)** Added `ZendExt_Paginator_Adapter_CallbackDecorator` that wraps a standard `Zend_Paginator_Adapter`, allowing to apply a callback to each element retrieved. this is a nice alternative to `array_map`, which can't be called on the paginator since it's iterable, but not an `array`.
 - **(Improvement)** `ZendExt_Service_Facebook` can now provide urls for profile pictures of different sizes and shapes.
 - **(Improvement)** Default list view for `ZendExt_Controller_CRUDAbstract` no longer displays the pagination controls if all data fits in one page.
 - **(Fix)** Prevent css in default list view for `ZendExt_Controller_CRUDAbstract` to impact the rest of the layout.
 - **(Improvement)** `ZendExt_Log_Writer_LazyStream` now has a factory method.
 - **(Improvement)** Added `ZendExt_Controller_Router_Route_Module`, which works just like `Zend_Controller_Router_Route_Module` except that it does not match unless the action actually exists in the controller.
 - **(Improvement)** `ZendExt_Service_Mailing` now sends a plain text copy of the email (generated by stripping tags from the original).
 - **(Improvement)** `ZendExt_Service_Mailing` receives a transport instead of creating one each time.
 - **(Fix)** `ZendExt_Controller_CRUDAbstract` is now aware of modules when generating urls.
 - **(Improvement)** Added `ZendExt_Session_SaveHandler_Memcache` to store sessions in memcached.
 - **(Improvement)** Added `zxa`, an automation tool to generate code from a db schema. Currently supports builders, models, tables and CRUDs.
 - **(Improvement)** Added `ZendExt_Db_Schema` to make handling of a database schema and metadata easier.

## 1.2
 - **(Improvement)** Crons now rely on `sys_getloadavg` instead of executing ad-hoc commands based on detection of the OS.
 - **(Improvement)** Refactored `ZendExt_Cron` classes for simpler code and allow creating named child processes and wait for them.
 - **(Improvement)** Added factory method for `ZendExt_Log_Writer_Mail`.
 - **(Improvement)** Style for update / new forms in `ZendExt_Controller_CRUDAbstract`.
 - **(Improvement)** Add links to navigate to new and update from the list in the default templates for `ZendExt_Controller_CRUDAbstract`.
 - **(Improvement)** Cleaned `ZendExt_Application_Resource_Multidb` and it's collaboration with `ZendExt_Dao_Abstract` for cleaner and simpler code.

## 1.1
 - **(Improvement)** Added `ZendExt_Controller_CRUDAbstract` to easily create CRUDs with scafolding.
 - **(Improvement)** Added `ZendExt_Service_Mailing` to easilly send email using views as templates.
 - **(Improvement)** Better config options for crons.

## 1.0
 - Initial release

