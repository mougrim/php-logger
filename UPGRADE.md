Upgrading Instructions for Php Logger v2
========================================

!!!IMPORTANT!!!

The following upgrading instructions are cumulative. That is, if you want to upgrade from version A to version C and there is version B between A and C, you need to follow the instructions for both A and B.

Upgrade from Php Logger v1.0.2
------------------------------

You have two ways:

- Fast way is to use [mougrim/php-logger-old-interface](https://github.com/mougrim/php-logger-old-interface) project and replace `\LoggerException` to `\Mougrim\Logger\LoggerException`.
- Right way is to replace next classes:
  - `\Logger` to `\Mougrim\Logger\Logger`
  - `\LoggerConfigurationException` to `\Mougrim\Logger\LoggerConfigurationException`
  - `\LoggerConfigurator` to `\Mougrim\Logger\LoggerConfigurator`
  - `\LoggerException` to `\Mougrim\Logger\LoggerException`
  - `\LoggerHierarchy` to `\Mougrim\Logger\LoggerHierarchy`
  - `\LoggerIOException` to `\Mougrim\Logger\LoggerIOException`
  - `\LoggerMDC` to `\Mougrim\Logger\LoggerMDC`
  - `\LoggerNDC` to `\Mougrim\Logger\LoggerNDC`
  - `\LoggerPolicy` to `\Mougrim\Logger\LoggerPolicy`
  - `\LoggerRender` to `\Mougrim\Logger\LoggerRender`
  - `\LoggerTimer` to `\Mougrim\Logger\LoggerTimer`
  - `\LoggerAppenderAbstract` to `\Mougrim\Logger\Appender\AppenderAbstract`
  - `\LoggerAppenderNull` to `\Mougrim\Logger\Appender\AppenderNull`
  - `\LoggerAppenderReopen` to `\Mougrim\Logger\Appender\AppenderReopen`
  - `\LoggerAppenderSay` to `\Mougrim\Logger\Appender\AppenderSay`
  - `\LoggerAppenderSocket` to `\Mougrim\Logger\Appender\AppenderSocket`
  - `\LoggerAppenderStd` to `\Mougrim\Logger\Appender\AppenderStd`
  - `\LoggerAppenderStream` to `\Mougrim\Logger\Appender\AppenderStream`
  - `\LoggerAppenderStreamBuffer` to `\Mougrim\Logger\Appender\AppenderStreamBuffer`
  - `\LoggerAppenderSyslog` to `\Mougrim\Logger\Appender\AppenderSyslog`
  - `\LoggerLayoutInterface` to `\Mougrim\Logger\Layout\LayoutInterface`
  - `\LoggerLayoutPattern` to `\Mougrim\Logger\Layout\LayoutPattern`
  - `\LoggerLayoutSimple` to `\Mougrim\Logger\Layout\LayoutSimple`
