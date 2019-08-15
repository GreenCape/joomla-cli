<?php

function jimport(string $path): void {}

/**
 * @method initialise()
 */
class JApplication {}

/**
 * @method static JApplication getApplication(string $application)
 * @method static JRegistry getConfig()
 */
class JFactory {}

/**
 * @method static JInstaller getInstance()
 * @method bool install(string $path)
 * @method JSimpleXML getManifest()
 */
class JInstaller {}

/**
 * @method static downloadPackage(string $source)
 * @method static unpack(string $tmpPath)
 */
class JInstallerHelper {}

/**
 * @method setValue(string $string, $value)
 * @method getValue(string $string)
 */
class JRegistry {}

/**
 * @method static string _(string $name)
 */
class JText {}

/**
 * @method string getShortVersion()
 * @method string getLongVersion()
 * @property-read string RELEASE
 */
class JVersion {}
