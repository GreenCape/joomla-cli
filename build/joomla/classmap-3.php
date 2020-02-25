<?php
return [
	'JRegistry'           => ['name' => '\\Joomla\\Registry\\Registry', 'type' => 'class'],
	'JRegistryFormat'     => ['name' => '\\Joomla\\Registry\\AbstractRegistryFormat', 'type' => 'abstract class'],
	'JRegistryFormatIni'  => ['name' => '\\Joomla\\Registry\\Format\\Ini', 'type' => 'class'],
	'JRegistryFormatJson' => ['name' => '\\Joomla\\Registry\\Format\\Json', 'type' => 'class'],
	'JRegistryFormatPhp'  => ['name' => '\\Joomla\\Registry\\Format\\Php', 'type' => 'class'],
	'JRegistryFormatXml'  => ['name' => '\\Joomla\\Registry\\Format\\Xml', 'type' => 'class'],
	'JStringInflector'    => ['name' => '\\Joomla\\String\\Inflector', 'type' => 'class'],
	'JStringNormalise'    => ['name' => '\\Joomla\\String\\Normalise', 'type' => 'class'],
	'JData'               => ['name' => '\\Joomla\\Data\\DataObject', 'type' => 'class'],
	'JDataSet'            => ['name' => '\\Joomla\\Data\\DataSet', 'type' => 'class'],
	'JDataDumpable'       => ['name' => '\\Joomla\\Data\\DumpableInterface', 'type' => 'interface'],

	'JApplicationAdministrator' => [
		'name' => '\\Joomla\\CMS\\Application\\AdministratorApplication',
		'type' => 'class'
	],
	'JApplicationHelper'        => ['name' => '\\Joomla\\CMS\\Application\\ApplicationHelper', 'type' => 'class'],
	'JApplicationBase'          => ['name' => '\\Joomla\\CMS\\Application\\BaseApplication', 'type' => 'class'],
	'JApplicationCli'           => ['name' => '\\Joomla\\CMS\\Application\\CliApplication', 'type' => 'class'],
	'JApplicationCms'           => ['name' => '\\Joomla\\CMS\\Application\\CMSApplication', 'type' => 'class'],
	'JApplicationDaemon'        => ['name' => '\\Joomla\\CMS\\Application\\DaemonApplication', 'type' => 'class'],
	'JApplicationSite'          => ['name' => '\\Joomla\\CMS\\Application\\SiteApplication', 'type' => 'class'],
	'JApplicationWeb'           => ['name' => '\\Joomla\\CMS\\Application\\WebApplication', 'type' => 'class'],
	'JApplicationWebClient'     => ['name' => '\\Joomla\\Application\\Web\\WebClient', 'type' => 'class'],
	'JDaemon'                   => ['name' => '\\Joomla\\CMS\\Application\\DaemonApplication', 'type' => 'class'],
	'JCli'                      => ['name' => '\\Joomla\\CMS\\Application\\CliApplication', 'type' => 'class'],
	'JWeb'                      => ['name' => '\\Joomla\\CMS\\Application\\WebApplication', 'type' => 'class'],
	'JWebClient'                => ['name' => '\\Joomla\\Application\\Web\\WebClient', 'type' => 'class'],

	'JModelAdmin'                  => ['name' => '\\Joomla\\CMS\\MVC\\Model\\AdminModel', 'type' => 'class'],
	'JModelForm'                   => ['name' => '\\Joomla\\CMS\\MVC\\Model\\FormModel', 'type' => 'class'],
	'JModelItem'                   => ['name' => '\\Joomla\\CMS\\MVC\\Model\\ItemModel', 'type' => 'class'],
	'JModelList'                   => ['name' => '\\Joomla\\CMS\\MVC\\Model\\ListModel', 'type' => 'class'],
	'JModelLegacy'                 => ['name' => '\\Joomla\\CMS\\MVC\\Model\\BaseDatabaseModel', 'type' => 'class'],
	'JViewCategories'              => ['name' => '\\Joomla\\CMS\\MVC\\View\\CategoriesView', 'type' => 'class'],
	'JViewCategory'                => ['name' => '\\Joomla\\CMS\\MVC\\View\\CategoryView', 'type' => 'class'],
	'JViewCategoryfeed'            => ['name' => '\\Joomla\\CMS\\MVC\\View\\CategoryFeedView', 'type' => 'class'],
	'JViewLegacy'                  => ['name' => '\\Joomla\\CMS\\MVC\\View\\HtmlView', 'type' => 'class'],
	'JControllerAdmin'             => ['name' => '\\Joomla\\CMS\\MVC\\Controller\\AdminController', 'type' => 'class'],
	'JControllerLegacy'            => ['name' => '\\Joomla\\CMS\\MVC\\Controller\\BaseController', 'type' => 'class'],
	'JControllerForm'              => ['name' => '\\Joomla\\CMS\\MVC\\Controller\\FormController', 'type' => 'class'],
	'JTableInterface'              => ['name' => '\\Joomla\\CMS\\Table\\TableInterface', 'type' => 'interface'],
	'JTable'                       => ['name' => '\\Joomla\\CMS\\Table\\Table', 'type' => 'class'],
	'JTableNested'                 => ['name' => '\\Joomla\\CMS\\Table\\Nested', 'type' => 'class'],
	'JTableAsset'                  => ['name' => '\\Joomla\\CMS\\Table\\Asset', 'type' => 'class'],
	'JTableExtension'              => ['name' => '\\Joomla\\CMS\\Table\\Extension', 'type' => 'class'],
	'JTableLanguage'               => ['name' => '\\Joomla\\CMS\\Table\\Language', 'type' => 'class'],
	'JTableUpdate'                 => ['name' => '\\Joomla\\CMS\\Table\\Update', 'type' => 'class'],
	'JTableUpdatesite'             => ['name' => '\\Joomla\\CMS\\Table\\UpdateSite', 'type' => 'class'],
	'JTableUser'                   => ['name' => '\\Joomla\\CMS\\Table\\User', 'type' => 'class'],
	'JTableUsergroup'              => ['name' => '\\Joomla\\CMS\\Table\\Usergroup', 'type' => 'class'],
	'JTableViewlevel'              => ['name' => '\\Joomla\\CMS\\Table\\ViewLevel', 'type' => 'class'],
	'JTableContenthistory'         => ['name' => '\\Joomla\\CMS\\Table\\ContentHistory', 'type' => 'class'],
	'JTableContenttype'            => ['name' => '\\Joomla\\CMS\\Table\\ContentType', 'type' => 'class'],
	'JTableCorecontent'            => ['name' => '\\Joomla\\CMS\\Table\\CoreContent', 'type' => 'class'],
	'JTableUcm'                    => ['name' => '\\Joomla\\CMS\\Table\\Ucm', 'type' => 'class'],
	'JTableCategory'               => ['name' => '\\Joomla\\CMS\\Table\\Category', 'type' => 'class'],
	'JTableContent'                => ['name' => '\\Joomla\\CMS\\Table\\Content', 'type' => 'class'],
	'JTableMenu'                   => ['name' => '\\Joomla\\CMS\\Table\\Menu', 'type' => 'class'],
	'JTableMenuType'               => ['name' => '\\Joomla\\CMS\\Table\\MenuType', 'type' => 'class'],
	'JTableModule'                 => ['name' => '\\Joomla\\CMS\\Table\\Module', 'type' => 'class'],
	'JTableObserver'               => ['name' => '\\Joomla\\CMS\\Table\\Observer\\AbstractObserver', 'type' => 'abstract class'],
	'JTableObserverContenthistory' => ['name' => '\\Joomla\\CMS\\Table\\Observer\\ContentHistory', 'type' => 'class'],
	'JTableObserverTags'           => ['name' => '\\Joomla\\CMS\\Table\\Observer\\Tags', 'type' => 'class'],

	'JAccess'                    => ['name' => '\\Joomla\\CMS\\Access\\Access', 'type' => 'class'],
	'JAccessRule'                => ['name' => '\\Joomla\\CMS\\Access\\Rule', 'type' => 'class'],
	'JAccessRules'               => ['name' => '\\Joomla\\CMS\\Access\\Rules', 'type' => 'class'],
	'JAccessWrapperAccess'       => ['name' => '\\Joomla\\CMS\\Access\\Wrapper\\Access', 'type' => 'class'],
	'JAccessExceptionNotallowed' => ['name' => '\\Joomla\\CMS\\Access\\Exception\\NotAllowed', 'type' => 'class'],
	'JRule'                      => ['name' => '\\Joomla\\CMS\\Access\\Rule', 'type' => 'class'],
	'JRules'                     => ['name' => '\\Joomla\\CMS\\Access\\Rules', 'type' => 'class'],

	'JHelp'    => ['name' => '\\Joomla\\CMS\\Help\\Help', 'type' => 'class'],
	'JCaptcha' => ['name' => '\\Joomla\\CMS\\Captcha\\Captcha', 'type' => 'class'],

	'JLanguageAssociations'         => ['name' => '\\Joomla\\CMS\\Language\\Associations', 'type' => 'class'],
	'JLanguage'                     => ['name' => '\\Joomla\\CMS\\Language\\Language', 'type' => 'class'],
	'JLanguageHelper'               => ['name' => '\\Joomla\\CMS\\Language\\LanguageHelper', 'type' => 'class'],
	'JLanguageStemmer'              => ['name' => '\\Joomla\\CMS\\Language\\LanguageStemmer', 'type' => 'class'],
	'JLanguageMultilang'            => ['name' => '\\Joomla\\CMS\\Language\\Multilanguage', 'type' => 'class'],
	'JText'                         => ['name' => '\\Joomla\\CMS\\Language\\Text', 'type' => 'class'],
	'JLanguageTransliterate'        => ['name' => '\\Joomla\\CMS\\Language\\Transliterate', 'type' => 'class'],
	'JLanguageStemmerPorteren'      => ['name' => '\\Joomla\\CMS\\Language\\Stemmer\\Porteren', 'type' => 'class'],
	'JLanguageWrapperText'          => ['name' => '\\Joomla\\CMS\\Language\\Wrapper\\JTextWrapper', 'type' => 'class'],
	'JLanguageWrapperHelper'        => [
		'name' => '\\Joomla\\CMS\\Language\\Wrapper\\LanguageHelperWrapper',
		'type' => 'class'
	],
	'JLanguageWrapperTransliterate' => [
		'name' => '\\Joomla\\CMS\\Language\\Wrapper\\TransliterateWrapper',
		'type' => 'class'
	],

	'JComponentHelper'                  => ['name' => '\\Joomla\\CMS\\Component\\ComponentHelper', 'type' => 'class'],
	'JComponentRecord'                  => ['name' => '\\Joomla\\CMS\\Component\\ComponentRecord', 'type' => 'class'],
	'JComponentExceptionMissing'        => [
		'name' => '\\Joomla\\CMS\\Component\\Exception\\MissingComponentException',
		'type' => 'class'
	],
	'JComponentRouterBase'              => [
		'name' => '\\Joomla\\CMS\\Component\\Router\\RouterBase',
		'type' => 'class'
	],
	'JComponentRouterInterface'         => [
		'name' => '\\Joomla\\CMS\\Component\\Router\\RouterInterface',
		'type' => 'interface'
	],
	'JComponentRouterLegacy'            => [
		'name' => '\\Joomla\\CMS\\Component\\Router\\RouterLegacy',
		'type' => 'class'
	],
	'JComponentRouterView'              => [
		'name' => '\\Joomla\\CMS\\Component\\Router\\RouterView',
		'type' => 'class'
	],
	'JComponentRouterViewconfiguration' => [
		'name' => '\\Joomla\\CMS\\Component\\Router\\RouterViewConfiguration',
		'type' => 'class'
	],
	'JComponentRouterRulesMenu'         => [
		'name' => '\\Joomla\\CMS\\Component\\Router\\Rules\\MenuRules',
		'type' => 'class'
	],
	'JComponentRouterRulesNomenu'       => [
		'name' => '\\Joomla\\CMS\\Component\\Router\\Rules\\NomenuRules',
		'type' => 'class'
	],
	'JComponentRouterRulesInterface'    => [
		'name' => '\\Joomla\\CMS\\Component\\Router\\Rules\\RulesInterface',
		'type' => 'interface'
	],
	'JComponentRouterRulesStandard'     => [
		'name' => '\\Joomla\\CMS\\Component\\Router\\Rules\\StandardRules',
		'type' => 'class'
	],

	'JEditor' => ['name' => '\\Joomla\\CMS\\Editor\\Editor', 'type' => 'class'],

	'JErrorPage' => ['name' => '\\Joomla\\CMS\\Exception\\ExceptionHandler', 'type' => 'class'],

	'JAuthenticationHelper' => ['name' => '\\Joomla\\CMS\\Helper\\AuthenticationHelper', 'type' => 'class'],
	'JHelper'               => ['name' => '\\Joomla\\CMS\\Helper\\CMSHelper', 'type' => 'class'],
	'JHelperContent'        => ['name' => '\\Joomla\\CMS\\Helper\\ContentHelper', 'type' => 'class'],
	'JHelperContenthistory' => ['name' => '\\Joomla\\CMS\\Helper\\ContentHistoryHelper', 'type' => 'class'],
	'JLibraryHelper'        => ['name' => '\\Joomla\\CMS\\Helper\\LibraryHelper', 'type' => 'class'],
	'JHelperMedia'          => ['name' => '\\Joomla\\CMS\\Helper\\MediaHelper', 'type' => 'class'],
	'JModuleHelper'         => ['name' => '\\Joomla\\CMS\\Helper\\ModuleHelper', 'type' => 'class'],
	'JHelperRoute'          => ['name' => '\\Joomla\\CMS\\Helper\\RouteHelper', 'type' => 'class'],
	'JSearchHelper'         => ['name' => '\\Joomla\\CMS\\Helper\\SearchHelper', 'type' => 'class'],
	'JHelperTags'           => ['name' => '\\Joomla\\CMS\\Helper\\TagsHelper', 'type' => 'class'],
	'JHelperUsergroups'     => ['name' => '\\Joomla\\CMS\\Helper\\UserGroupsHelper', 'type' => 'class'],

	'JLayoutBase'   => ['name' => '\\Joomla\\CMS\\Layout\\BaseLayout', 'type' => 'class'],
	'JLayoutFile'   => ['name' => '\\Joomla\\CMS\\Layout\\FileLayout', 'type' => 'class'],
	'JLayoutHelper' => ['name' => '\\Joomla\\CMS\\Layout\\LayoutHelper', 'type' => 'class'],
	'JLayout'       => ['name' => '\\Joomla\\CMS\\Layout\\LayoutInterface', 'type' => 'interface'],

	'JResponseJson' => ['name' => '\\Joomla\\CMS\\Response\\JsonResponse', 'type' => 'class'],

	'JPlugin'       => ['name' => '\\Joomla\\CMS\\Plugin\\CMSPlugin', 'type' => 'class'],
	'JPluginHelper' => ['name' => '\\Joomla\\CMS\\Plugin\\PluginHelper', 'type' => 'class'],

	'JMenu'              => ['name' => '\\Joomla\\CMS\\Menu\\AbstractMenu', 'type' => 'abstract class'],
	'JMenuAdministrator' => ['name' => '\\Joomla\\CMS\\Menu\\AdministratorMenu', 'type' => 'class'],
	'JMenuItem'          => ['name' => '\\Joomla\\CMS\\Menu\\MenuItem', 'type' => 'class'],
	'JMenuSite'          => ['name' => '\\Joomla\\CMS\\Menu\\SiteMenu', 'type' => 'class'],

	'JPagination'       => ['name' => '\\Joomla\\CMS\\Pagination\\Pagination', 'type' => 'class'],
	'JPaginationObject' => ['name' => '\\Joomla\\CMS\\Pagination\\PaginationObject', 'type' => 'class'],

	'JPathway'     => ['name' => '\\Joomla\\CMS\\Pathway\\Pathway', 'type' => 'class'],
	'JPathwaySite' => ['name' => '\\Joomla\\CMS\\Pathway\\SitePathway', 'type' => 'class'],

	'JSchemaChangeitem'           => ['name' => '\\Joomla\\CMS\\Schema\\ChangeItem', 'type' => 'class'],
	'JSchemaChangeset'            => ['name' => '\\Joomla\\CMS\\Schema\\ChangeSet', 'type' => 'class'],
	'JSchemaChangeitemMysql'      => [
		'name' => '\\Joomla\\CMS\\Schema\\ChangeItem\\MysqlChangeItem',
		'type' => 'class'
	],
	'JSchemaChangeitemPostgresql' => [
		'name' => '\\Joomla\\CMS\\Schema\\ChangeItem\\PostgresqlChangeItem',
		'type' => 'class'
	],
	'JSchemaChangeitemSqlsrv'     => [
		'name' => '\\Joomla\\CMS\\Schema\\ChangeItem\\SqlsrvChangeItem',
		'type' => 'class'
	],

	'JUcm'        => ['name' => '\\Joomla\\CMS\\UCM\\UCM', 'type' => 'class'],
	'JUcmBase'    => ['name' => '\\Joomla\\CMS\\UCM\\UCMBase', 'type' => 'class'],
	'JUcmContent' => ['name' => '\\Joomla\\CMS\\UCM\\UCMContent', 'type' => 'class'],
	'JUcmType'    => ['name' => '\\Joomla\\CMS\\UCM\\UCMType', 'type' => 'class'],

	'JToolbar'                => ['name' => '\\Joomla\\CMS\\Toolbar\\Toolbar', 'type' => 'class'],
	'JToolbarButton'          => ['name' => '\\Joomla\\CMS\\Toolbar\\ToolbarButton', 'type' => 'class'],
	'JToolbarButtonConfirm'   => ['name' => '\\Joomla\\CMS\\Toolbar\\Button\\ConfirmButton', 'type' => 'class'],
	'JToolbarButtonCustom'    => ['name' => '\\Joomla\\CMS\\Toolbar\\Button\\CustomButton', 'type' => 'class'],
	'JToolbarButtonHelp'      => ['name' => '\\Joomla\\CMS\\Toolbar\\Button\\HelpButton', 'type' => 'class'],
	'JToolbarButtonLink'      => ['name' => '\\Joomla\\CMS\\Toolbar\\Button\\LinkButton', 'type' => 'class'],
	'JToolbarButtonPopup'     => ['name' => '\\Joomla\\CMS\\Toolbar\\Button\\PopupButton', 'type' => 'class'],
	'JToolbarButtonSeparator' => ['name' => '\\Joomla\\CMS\\Toolbar\\Button\\SeparatorButton', 'type' => 'class'],
	'JToolbarButtonSlider'    => ['name' => '\\Joomla\\CMS\\Toolbar\\Button\\SliderButton', 'type' => 'class'],
	'JToolbarButtonStandard'  => ['name' => '\\Joomla\\CMS\\Toolbar\\Button\\StandardButton', 'type' => 'class'],
	'JButton'                 => ['name' => '\\Joomla\\CMS\\Toolbar\\ToolbarButton', 'type' => 'class'],

	'JVersion' => ['name' => '\\Joomla\\CMS\\Version', 'type' => 'class'],

	'JAuthentication'         => ['name' => '\\Joomla\\CMS\\Authentication\\Authentication', 'type' => 'class'],
	'JAuthenticationResponse' => ['name' => '\\Joomla\\CMS\\Authentication\\AuthenticationResponse', 'type' => 'class'],

	'JBrowser' => ['name' => '\\Joomla\\CMS\\Environment\\Browser', 'type' => 'class'],

	'JAssociationExtensionInterface' => [
		'name' => '\\Joomla\\CMS\\Association\\AssociationExtensionInterface',
		'type' => 'interface'
	],
	'JAssociationExtensionHelper'    => [
		'name' => '\\Joomla\\CMS\\Association\\AssociationExtensionHelper',
		'type' => 'class'
	],

	'JDocument'                      => ['name' => '\\Joomla\\CMS\\Document\\Document', 'type' => 'class'],
	'JDocumentError'                 => ['name' => '\\Joomla\\CMS\\Document\\ErrorDocument', 'type' => 'class'],
	'JDocumentFeed'                  => ['name' => '\\Joomla\\CMS\\Document\\FeedDocument', 'type' => 'class'],
	'JDocumentHtml'                  => ['name' => '\\Joomla\\CMS\\Document\\HtmlDocument', 'type' => 'class'],
	'JDocumentImage'                 => ['name' => '\\Joomla\\CMS\\Document\\ImageDocument', 'type' => 'class'],
	'JDocumentJson'                  => ['name' => '\\Joomla\\CMS\\Document\\JsonDocument', 'type' => 'class'],
	'JDocumentOpensearch'            => ['name' => '\\Joomla\\CMS\\Document\\OpensearchDocument', 'type' => 'class'],
	'JDocumentRaw'                   => ['name' => '\\Joomla\\CMS\\Document\\RawDocument', 'type' => 'class'],
	'JDocumentRenderer'              => ['name' => '\\Joomla\\CMS\\Document\\DocumentRenderer', 'type' => 'class'],
	'JDocumentXml'                   => ['name' => '\\Joomla\\CMS\\Document\\XmlDocument', 'type' => 'class'],
	'JDocumentRendererFeedAtom'      => [
		'name' => '\\Joomla\\CMS\\Document\\Renderer\\Feed\\AtomRenderer',
		'type' => 'class'
	],
	'JDocumentRendererFeedRss'       => [
		'name' => '\\Joomla\\CMS\\Document\\Renderer\\Feed\\RssRenderer',
		'type' => 'class'
	],
	'JDocumentRendererHtmlComponent' => [
		'name' => '\\Joomla\\CMS\\Document\\Renderer\\Html\\ComponentRenderer',
		'type' => 'class'
	],
	'JDocumentRendererHtmlHead'      => [
		'name' => '\\Joomla\\CMS\\Document\\Renderer\\Html\\HeadRenderer',
		'type' => 'class'
	],
	'JDocumentRendererHtmlMessage'   => [
		'name' => '\\Joomla\\CMS\\Document\\Renderer\\Html\\MessageRenderer',
		'type' => 'class'
	],
	'JDocumentRendererHtmlModule'    => [
		'name' => '\\Joomla\\CMS\\Document\\Renderer\\Html\\ModuleRenderer',
		'type' => 'class'
	],
	'JDocumentRendererHtmlModules'   => [
		'name' => '\\Joomla\\CMS\\Document\\Renderer\\Html\\ModulesRenderer',
		'type' => 'class'
	],
	'JDocumentRendererAtom'          => [
		'name' => '\\Joomla\\CMS\\Document\\Renderer\\Feed\\AtomRenderer',
		'type' => 'class'
	],
	'JDocumentRendererRSS'           => [
		'name' => '\\Joomla\\CMS\\Document\\Renderer\\Feed\\RssRenderer',
		'type' => 'class'
	],
	'JDocumentRendererComponent'     => [
		'name' => '\\Joomla\\CMS\\Document\\Renderer\\Html\\ComponentRenderer',
		'type' => 'class'
	],
	'JDocumentRendererHead'          => [
		'name' => '\\Joomla\\CMS\\Document\\Renderer\\Html\\HeadRenderer',
		'type' => 'class'
	],
	'JDocumentRendererMessage'       => [
		'name' => '\\Joomla\\CMS\\Document\\Renderer\\Html\\MessageRenderer',
		'type' => 'class'
	],
	'JDocumentRendererModule'        => [
		'name' => '\\Joomla\\CMS\\Document\\Renderer\\Html\\ModuleRenderer',
		'type' => 'class'
	],
	'JDocumentRendererModules'       => [
		'name' => '\\Joomla\\CMS\\Document\\Renderer\\Html\\ModulesRenderer',
		'type' => 'class'
	],
	'JFeedEnclosure'                 => ['name' => '\\Joomla\\CMS\\Document\\Feed\\FeedEnclosure', 'type' => 'class'],
	'JFeedImage'                     => ['name' => '\\Joomla\\CMS\\Document\\Feed\\FeedImage', 'type' => 'class'],
	'JFeedItem'                      => ['name' => '\\Joomla\\CMS\\Document\\Feed\\FeedItem', 'type' => 'class'],
	'JOpenSearchImage'               => [
		'name' => '\\Joomla\\CMS\\Document\\Opensearch\\OpensearchImage',
		'type' => 'class'
	],
	'JOpenSearchUrl'                 => [
		'name' => '\\Joomla\\CMS\\Document\\Opensearch\\OpensearchUrl',
		'type' => 'class'
	],

	'JFilterInput'         => ['name' => '\\Joomla\\CMS\\Filter\\InputFilter', 'type' => 'class'],
	'JFilterOutput'        => ['name' => '\\Joomla\\CMS\\Filter\\OutputFilter', 'type' => 'class'],
	'JFilterWrapperOutput' => ['name' => '\\Joomla\\CMS\\Filter\\Wrapper\\OutputFilterWrapper', 'type' => 'class'],

	'JHttp'                => ['name' => '\\Joomla\\CMS\\Http\\Http', 'type' => 'class'],
	'JHttpFactory'         => ['name' => '\\Joomla\\CMS\\Http\\HttpFactory', 'type' => 'class'],
	'JHttpResponse'        => ['name' => '\\Joomla\\CMS\\Http\\Response', 'type' => 'class'],
	'JHttpTransport'       => ['name' => '\\Joomla\\CMS\\Http\\TransportInterface', 'type' => 'interface'],
	'JHttpTransportCurl'   => ['name' => '\\Joomla\\CMS\\Http\\Transport\\CurlTransport', 'type' => 'class'],
	'JHttpTransportSocket' => ['name' => '\\Joomla\\CMS\\Http\\Transport\\SocketTransport', 'type' => 'class'],
	'JHttpTransportStream' => ['name' => '\\Joomla\\CMS\\Http\\Transport\\StreamTransport', 'type' => 'class'],
	'JHttpWrapperFactory'  => ['name' => '\\Joomla\\CMS\\Http\\Wrapper\\FactoryWrapper', 'type' => 'class'],

	'JInstaller'                 => ['name' => '\\Joomla\\CMS\\Installer\\Installer', 'type' => 'class'],
	'JInstallerAdapter'          => ['name' => '\\Joomla\\CMS\\Installer\\InstallerAdapter', 'type' => 'class'],
	'JInstallerExtension'        => ['name' => '\\Joomla\\CMS\\Installer\\InstallerExtension', 'type' => 'class'],
	'JExtension'                 => ['name' => '\\Joomla\\CMS\\Installer\\InstallerExtension', 'type' => 'class'],
	'JInstallerHelper'           => ['name' => '\\Joomla\\CMS\\Installer\\InstallerHelper', 'type' => 'class'],
	'JInstallerScript'           => ['name' => '\\Joomla\\CMS\\Installer\\InstallerScript', 'type' => 'class'],
	'JInstallerManifest'         => ['name' => '\\Joomla\\CMS\\Installer\\Manifest', 'type' => 'class'],
	'JInstallerAdapterComponent' => [
		'name' => '\\Joomla\\CMS\\Installer\\Adapter\\ComponentAdapter',
		'type' => 'class'
	],
	'JInstallerComponent'        => [
		'name' => '\\Joomla\\CMS\\Installer\\Adapter\\ComponentAdapter',
		'type' => 'class'
	],
	'JInstallerAdapterFile'      => ['name' => '\\Joomla\\CMS\\Installer\\Adapter\\FileAdapter', 'type' => 'class'],
	'JInstallerFile'             => ['name' => '\\Joomla\\CMS\\Installer\\Adapter\\FileAdapter', 'type' => 'class'],
	'JInstallerAdapterLanguage'  => ['name' => '\\Joomla\\CMS\\Installer\\Adapter\\LanguageAdapter', 'type' => 'class'],
	'JInstallerLanguage'         => ['name' => '\\Joomla\\CMS\\Installer\\Adapter\\LanguageAdapter', 'type' => 'class'],
	'JInstallerAdapterLibrary'   => ['name' => '\\Joomla\\CMS\\Installer\\Adapter\\LibraryAdapter', 'type' => 'class'],
	'JInstallerLibrary'          => ['name' => '\\Joomla\\CMS\\Installer\\Adapter\\LibraryAdapter', 'type' => 'class'],
	'JInstallerAdapterModule'    => ['name' => '\\Joomla\\CMS\\Installer\\Adapter\\ModuleAdapter', 'type' => 'class'],
	'JInstallerModule'           => ['name' => '\\Joomla\\CMS\\Installer\\Adapter\\ModuleAdapter', 'type' => 'class'],
	'JInstallerAdapterPackage'   => ['name' => '\\Joomla\\CMS\\Installer\\Adapter\\PackageAdapter', 'type' => 'class'],
	'JInstallerPackage'          => ['name' => '\\Joomla\\CMS\\Installer\\Adapter\\PackageAdapter', 'type' => 'class'],
	'JInstallerAdapterPlugin'    => ['name' => '\\Joomla\\CMS\\Installer\\Adapter\\PluginAdapter', 'type' => 'class'],
	'JInstallerPlugin'           => ['name' => '\\Joomla\\CMS\\Installer\\Adapter\\PluginAdapter', 'type' => 'class'],
	'JInstallerAdapterTemplate'  => ['name' => '\\Joomla\\CMS\\Installer\\Adapter\\TemplateAdapter', 'type' => 'class'],
	'JInstallerTemplate'         => ['name' => '\\Joomla\\CMS\\Installer\\Adapter\\TemplateAdapter', 'type' => 'class'],
	'JInstallerManifestLibrary'  => [
		'name' => '\\Joomla\\CMS\\Installer\\Manifest\\LibraryManifest',
		'type' => 'class'
	],
	'JInstallerManifestPackage'  => [
		'name' => '\\Joomla\\CMS\\Installer\\Manifest\\PackageManifest',
		'type' => 'class'
	],

	'JRouterAdministrator' => ['name' => '\\Joomla\\CMS\\Router\\AdministratorRouter', 'type' => 'class'],
	'JRoute'               => ['name' => '\\Joomla\\CMS\\Router\\Route', 'type' => 'class'],
	'JRouter'              => ['name' => '\\Joomla\\CMS\\Router\\Router', 'type' => 'class'],
	'JRouterSite'          => ['name' => '\\Joomla\\CMS\\Router\\SiteRouter', 'type' => 'class'],

	'JCategories'   => ['name' => '\\Joomla\\CMS\\Categories\\Categories', 'type' => 'class'],
	'JCategoryNode' => ['name' => '\\Joomla\\CMS\\Categories\\CategoryNode', 'type' => 'class'],

	'JDate' => ['name' => '\\Joomla\\CMS\\Date\\Date', 'type' => 'class'],

	'JLog'                    => ['name' => '\\Joomla\\CMS\\Log\\Log', 'type' => 'class'],
	'JLogEntry'               => ['name' => '\\Joomla\\CMS\\Log\\LogEntry', 'type' => 'class'],
	'JLogLogger'              => ['name' => '\\Joomla\\CMS\\Log\\Logger', 'type' => 'class'],
	'JLogger'                 => ['name' => '\\Joomla\\CMS\\Log\\Logger', 'type' => 'class'],
	'JLogLoggerCallback'      => ['name' => '\\Joomla\\CMS\\Log\\Logger\\CallbackLogger', 'type' => 'class'],
	'JLogLoggerDatabase'      => ['name' => '\\Joomla\\CMS\\Log\\Logger\\DatabaseLogger', 'type' => 'class'],
	'JLogLoggerEcho'          => ['name' => '\\Joomla\\CMS\\Log\\Logger\\EchoLogger', 'type' => 'class'],
	'JLogLoggerFormattedtext' => ['name' => '\\Joomla\\CMS\\Log\\Logger\\FormattedtextLogger', 'type' => 'class'],
	'JLogLoggerMessagequeue'  => ['name' => '\\Joomla\\CMS\\Log\\Logger\\MessagequeueLogger', 'type' => 'class'],
	'JLogLoggerSyslog'        => ['name' => '\\Joomla\\CMS\\Log\\Logger\\SyslogLogger', 'type' => 'class'],
	'JLogLoggerW3c'           => ['name' => '\\Joomla\\CMS\\Log\\Logger\\W3cLogger', 'type' => 'class'],

	'JProfiler' => ['name' => '\\Joomla\\CMS\\Profiler\\Profiler', 'type' => 'class'],

	'JUri' => ['name' => '\\Joomla\\CMS\\Uri\\Uri', 'type' => 'class'],

	'JCache'                     => ['name' => '\\Joomla\\CMS\\Cache\\Cache', 'type' => 'class'],
	'JCacheController'           => ['name' => '\\Joomla\\CMS\\Cache\\CacheController', 'type' => 'class'],
	'JCacheStorage'              => ['name' => '\\Joomla\\CMS\\Cache\\CacheStorage', 'type' => 'class'],
	'JCacheControllerCallback'   => [
		'name' => '\\Joomla\\CMS\\Cache\\Controller\\CallbackController',
		'type' => 'class'
	],
	'JCacheControllerOutput'     => ['name' => '\\Joomla\\CMS\\Cache\\Controller\\OutputController', 'type' => 'class'],
	'JCacheControllerPage'       => ['name' => '\\Joomla\\CMS\\Cache\\Controller\\PageController', 'type' => 'class'],
	'JCacheControllerView'       => ['name' => '\\Joomla\\CMS\\Cache\\Controller\\ViewController', 'type' => 'class'],
	'JCacheStorageApc'           => ['name' => '\\Joomla\\CMS\\Cache\\Storage\\ApcStorage', 'type' => 'class'],
	'JCacheStorageApcu'          => ['name' => '\\Joomla\\CMS\\Cache\\Storage\\ApcuStorage', 'type' => 'class'],
	'JCacheStorageHelper'        => ['name' => '\\Joomla\\CMS\\Cache\\Storage\\CacheStorageHelper', 'type' => 'class'],
	'JCacheStorageCachelite'     => ['name' => '\\Joomla\\CMS\\Cache\\Storage\\CacheliteStorage', 'type' => 'class'],
	'JCacheStorageFile'          => ['name' => '\\Joomla\\CMS\\Cache\\Storage\\FileStorage', 'type' => 'class'],
	'JCacheStorageMemcached'     => ['name' => '\\Joomla\\CMS\\Cache\\Storage\\MemcachedStorage', 'type' => 'class'],
	'JCacheStorageMemcache'      => ['name' => '\\Joomla\\CMS\\Cache\\Storage\\MemcacheStorage', 'type' => 'class'],
	'JCacheStorageRedis'         => ['name' => '\\Joomla\\CMS\\Cache\\Storage\\RedisStorage', 'type' => 'class'],
	'JCacheStorageWincache'      => ['name' => '\\Joomla\\CMS\\Cache\\Storage\\WincacheStorage', 'type' => 'class'],
	'JCacheStorageXcache'        => ['name' => '\\Joomla\\CMS\\Cache\\Storage\\XcacheStorage', 'type' => 'class'],
	'JCacheException'            => [
		'name' => '\\Joomla\\CMS\\Cache\\Exception\\CacheExceptionInterface',
		'type' => 'interface'
	],
	'JCacheExceptionConnecting'  => [
		'name' => '\\Joomla\\CMS\\Cache\\Exception\\CacheConnectingException',
		'type' => 'class'
	],
	'JCacheExceptionUnsupported' => [
		'name' => '\\Joomla\\CMS\\Cache\\Exception\\UnsupportedCacheException',
		'type' => 'class'
	],

	'JSession'                     => ['name' => '\\Joomla\\CMS\\Session\\Session', 'type' => 'class'],
	'JSessionExceptionUnsupported' => [
		'name' => '\\Joomla\\CMS\\Session\\Exception\\UnsupportedStorageException',
		'type' => 'class'
	],

	'JUser'              => ['name' => '\\Joomla\\CMS\\User\\User', 'type' => 'class'],
	'JUserHelper'        => ['name' => '\\Joomla\\CMS\\User\\UserHelper', 'type' => 'class'],
	'JUserWrapperHelper' => ['name' => '\\Joomla\\CMS\\User\\UserWrapper', 'type' => 'class'],

	'JForm'                           => ['name' => '\\Joomla\\CMS\\Form\\Form', 'type' => 'class'],
	'JFormField'                      => ['name' => '\\Joomla\\CMS\\Form\\FormField', 'type' => 'class'],
	'JFormHelper'                     => ['name' => '\\Joomla\\CMS\\Form\\FormHelper', 'type' => 'class'],
	'JFormRule'                       => ['name' => '\\Joomla\\CMS\\Form\\FormRule', 'type' => 'class'],
	'JFormWrapper'                    => ['name' => '\\Joomla\\CMS\\Form\\FormWrapper', 'type' => 'class'],
	'JFormFieldAuthor'                => ['name' => '\\Joomla\\CMS\\Form\\Field\\AuthorField', 'type' => 'class'],
	'JFormFieldCaptcha'               => ['name' => '\\Joomla\\CMS\\Form\\Field\\CaptchaField', 'type' => 'class'],
	'JFormFieldChromeStyle'           => ['name' => '\\Joomla\\CMS\\Form\\Field\\ChromestyleField', 'type' => 'class'],
	'JFormFieldContenthistory'        => [
		'name' => '\\Joomla\\CMS\\Form\\Field\\ContenthistoryField',
		'type' => 'class'
	],
	'JFormFieldContentlanguage'       => [
		'name' => '\\Joomla\\CMS\\Form\\Field\\ContentlanguageField',
		'type' => 'class'
	],
	'JFormFieldContenttype'           => ['name' => '\\Joomla\\CMS\\Form\\Field\\ContenttypeField', 'type' => 'class'],
	'JFormFieldEditor'                => ['name' => '\\Joomla\\CMS\\Form\\Field\\EditorField', 'type' => 'class'],
	'JFormFieldFrontend_Language'     => [
		'name' => '\\Joomla\\CMS\\Form\\Field\\FrontendlanguageField',
		'type' => 'class'
	],
	'JFormFieldHeadertag'             => ['name' => '\\Joomla\\CMS\\Form\\Field\\HeadertagField', 'type' => 'class'],
	'JFormFieldHelpsite'              => ['name' => '\\Joomla\\CMS\\Form\\Field\\HelpsiteField', 'type' => 'class'],
	'JFormFieldLastvisitDateRange'    => [
		'name' => '\\Joomla\\CMS\\Form\\Field\\LastvisitdaterangeField',
		'type' => 'class'
	],
	'JFormFieldLimitbox'              => ['name' => '\\Joomla\\CMS\\Form\\Field\\LimitboxField', 'type' => 'class'],
	'JFormFieldMedia'                 => ['name' => '\\Joomla\\CMS\\Form\\Field\\MediaField', 'type' => 'class'],
	'JFormFieldMenu'                  => ['name' => '\\Joomla\\CMS\\Form\\Field\\MenuField', 'type' => 'class'],
	'JFormFieldMenuitem'              => ['name' => '\\Joomla\\CMS\\Form\\Field\\MenuitemField', 'type' => 'class'],
	'JFormFieldModuleOrder'           => ['name' => '\\Joomla\\CMS\\Form\\Field\\ModuleorderField', 'type' => 'class'],
	'JFormFieldModulePosition'        => [
		'name' => '\\Joomla\\CMS\\Form\\Field\\ModulepositionField',
		'type' => 'class'
	],
	'JFormFieldModuletag'             => ['name' => '\\Joomla\\CMS\\Form\\Field\\ModuletagField', 'type' => 'class'],
	'JFormFieldOrdering'              => ['name' => '\\Joomla\\CMS\\Form\\Field\\OrderingField', 'type' => 'class'],
	'JFormFieldPlugin_Status'         => ['name' => '\\Joomla\\CMS\\Form\\Field\\PluginstatusField', 'type' => 'class'],
	'JFormFieldRedirect_Status'       => [
		'name' => '\\Joomla\\CMS\\Form\\Field\\RedirectStatusField',
		'type' => 'class'
	],
	'JFormFieldRegistrationDateRange' => [
		'name' => '\\Joomla\\CMS\\Form\\Field\\RegistrationdaterangeField',
		'type' => 'class'
	],
	'JFormFieldStatus'                => ['name' => '\\Joomla\\CMS\\Form\\Field\\StatusField', 'type' => 'class'],
	'JFormFieldTag'                   => ['name' => '\\Joomla\\CMS\\Form\\Field\\TagField', 'type' => 'class'],
	'JFormFieldTemplatestyle'         => [
		'name' => '\\Joomla\\CMS\\Form\\Field\\TemplatestyleField',
		'type' => 'class'
	],
	'JFormFieldUserActive'            => ['name' => '\\Joomla\\CMS\\Form\\Field\\UseractiveField', 'type' => 'class'],
	'JFormFieldUserGroupList'         => [
		'name' => '\\Joomla\\CMS\\Form\\Field\\UsergrouplistField',
		'type' => 'class'
	],
	'JFormFieldUserState'             => ['name' => '\\Joomla\\CMS\\Form\\Field\\UserstateField', 'type' => 'class'],
	'JFormFieldUser'                  => ['name' => '\\Joomla\\CMS\\Form\\Field\\UserField', 'type' => 'class'],
	'JFormRuleBoolean'                => ['name' => '\\Joomla\\CMS\\Form\\Rule\\BooleanRule', 'type' => 'class'],
	'JFormRuleCalendar'               => ['name' => '\\Joomla\\CMS\\Form\\Rule\\CalendarRule', 'type' => 'class'],
	'JFormRuleCaptcha'                => ['name' => '\\Joomla\\CMS\\Form\\Rule\\CaptchaRule', 'type' => 'class'],
	'JFormRuleColor'                  => ['name' => '\\Joomla\\CMS\\Form\\Rule\\ColorRule', 'type' => 'class'],
	'JFormRuleEmail'                  => ['name' => '\\Joomla\\CMS\\Form\\Rule\\EmailRule', 'type' => 'class'],
	'JFormRuleEquals'                 => ['name' => '\\Joomla\\CMS\\Form\\Rule\\EqualsRule', 'type' => 'class'],
	'JFormRuleNotequals'              => ['name' => '\\Joomla\\CMS\\Form\\Rule\\NotequalsRule', 'type' => 'class'],
	'JFormRuleNumber'                 => ['name' => '\\Joomla\\CMS\\Form\\Rule\\NumberRule', 'type' => 'class'],
	'JFormRuleOptions'                => ['name' => '\\Joomla\\CMS\\Form\\Rule\\OptionsRule', 'type' => 'class'],
	'JFormRulePassword'               => ['name' => '\\Joomla\\CMS\\Form\\Rule\\PasswordRule', 'type' => 'class'],
	'JFormRuleRules'                  => ['name' => '\\Joomla\\CMS\\Form\\Rule\\RulesRule', 'type' => 'class'],
	'JFormRuleTel'                    => ['name' => '\\Joomla\\CMS\\Form\\Rule\\TelRule', 'type' => 'class'],
	'JFormRuleUrl'                    => ['name' => '\\Joomla\\CMS\\Form\\Rule\\UrlRule', 'type' => 'class'],
	'JFormRuleUsername'               => ['name' => '\\Joomla\\CMS\\Form\\Rule\\UsernameRule', 'type' => 'class'],

	'JMicrodata' => ['name' => '\\Joomla\\CMS\\Microdata\\Microdata', 'type' => 'class'],

	'JFactory' => ['name' => '\\Joomla\\CMS\\Factory', 'type' => 'class'],

	'JMail'              => ['name' => '\\Joomla\\CMS\\Mail\\Mail', 'type' => 'class'],
	'JMailHelper'        => ['name' => '\\Joomla\\CMS\\Mail\\MailHelper', 'type' => 'class'],
	'JMailWrapperHelper' => ['name' => '\\Joomla\\CMS\\Mail\\MailWrapper', 'type' => 'class'],

	'JClientHelper'        => ['name' => '\\Joomla\\CMS\\Client\\ClientHelper', 'type' => 'class'],
	'JClientWrapperHelper' => ['name' => '\\Joomla\\CMS\\Client\\ClientWrapper', 'type' => 'class'],
	'JClientFtp'           => ['name' => '\\Joomla\\CMS\\Client\\FtpClient', 'type' => 'class'],
	'JFTP'                 => ['name' => '\\Joomla\\CMS\\Client\\FtpClient', 'type' => 'class'],
	'JClientLdap'          => ['name' => '\\Joomla\\Ldap\\LdapClient', 'type' => 'class'],
	'JLDAP'                => ['name' => '\\Joomla\\Ldap\\LdapClient', 'type' => 'class'],

	'JUpdate'            => ['name' => '\\Joomla\\CMS\\Updater\\Update', 'type' => 'class'],
	'JUpdateAdapter'     => ['name' => '\\Joomla\\CMS\\Updater\\UpdateAdapter', 'type' => 'class'],
	'JUpdater'           => ['name' => '\\Joomla\\CMS\\Updater\\Updater', 'type' => 'class'],
	'JUpdaterCollection' => ['name' => '\\Joomla\\CMS\\Updater\\Adapter\\CollectionAdapter', 'type' => 'class'],
	'JUpdaterExtension'  => ['name' => '\\Joomla\\CMS\\Updater\\Adapter\\ExtensionAdapter', 'type' => 'class'],

	'JCrypt'                  => ['name' => '\\Joomla\\CMS\\Crypt\\Crypt', 'type' => 'class'],
	'JCryptCipher'            => ['name' => '\\Joomla\\CMS\\Crypt\\CipherInterface', 'type' => 'class'],
	'JCryptKey'               => ['name' => '\\Joomla\\CMS\\Crypt\\Key', 'type' => 'class'],
	'JCryptPassword'          => ['name' => '\\Joomla\\CMS\\Crypt\\CryptPassword', 'type' => 'class'],
	'JCryptCipherBlowfish'    => ['name' => '\\Joomla\\CMS\\Crypt\\Cipher\\BlowfishCipher', 'type' => 'class'],
	'JCryptCipherCrypto'      => ['name' => '\\Joomla\\CMS\\Crypt\\Cipher\\CryptoCipher', 'type' => 'class'],
	'JCryptCipherMcrypt'      => ['name' => '\\Joomla\\CMS\\Crypt\\Cipher\\McryptCipher', 'type' => 'class'],
	'JCryptCipherRijndael256' => ['name' => '\\Joomla\\CMS\\Crypt\\Cipher\\Rijndael256Cipher', 'type' => 'class'],
	'JCryptCipherSimple'      => ['name' => '\\Joomla\\CMS\\Crypt\\Cipher\\SimpleCipher', 'type' => 'class'],
	'JCryptCipherSodium'      => ['name' => '\\Joomla\\CMS\\Crypt\\Cipher\\SodiumCipher', 'type' => 'class'],
	'JCryptCipher3Des'        => ['name' => '\\Joomla\\CMS\\Crypt\\Cipher\\TripleDesCipher', 'type' => 'class'],
	'JCryptPasswordSimple'    => ['name' => '\\Joomla\\CMS\\Crypt\\Password\\SimpleCryptPassword', 'type' => 'class'],

	'JStringPunycode' => ['name' => '\\Joomla\\CMS\\String\\PunycodeHelper', 'type' => 'class'],

	'JBuffer'  => ['name' => '\\Joomla\\CMS\\Utility\\BufferStreamHandler', 'type' => 'class'],
	'JUtility' => ['name' => '\\Joomla\\CMS\\Utility\\Utility', 'type' => 'class'],

	'JInputCli'    => ['name' => '\\Joomla\\CMS\\Input\\Cli', 'type' => 'class'],
	'JInputCookie' => ['name' => '\\Joomla\\CMS\\Input\\Cookie', 'type' => 'class'],
	'JInputFiles'  => ['name' => '\\Joomla\\CMS\\Input\\Files', 'type' => 'class'],
	'JInput'       => ['name' => '\\Joomla\\CMS\\Input\\Input', 'type' => 'class'],
	'JInputJSON'   => ['name' => '\\Joomla\\CMS\\Input\\Json', 'type' => 'class'],

	'JFeed'                => ['name' => '\\Joomla\\CMS\\Feed\\Feed', 'type' => 'class'],
	'JFeedEntry'           => ['name' => '\\Joomla\\CMS\\Feed\\FeedEntry', 'type' => 'class'],
	'JFeedFactory'         => ['name' => '\\Joomla\\CMS\\Feed\\FeedFactory', 'type' => 'class'],
	'JFeedLink'            => ['name' => '\\Joomla\\CMS\\Feed\\FeedLink', 'type' => 'class'],
	'JFeedParser'          => ['name' => '\\Joomla\\CMS\\Feed\\FeedParser', 'type' => 'class'],
	'JFeedPerson'          => ['name' => '\\Joomla\\CMS\\Feed\\FeedPerson', 'type' => 'class'],
	'JFeedParserAtom'      => ['name' => '\\Joomla\\CMS\\Feed\\Parser\\AtomParser', 'type' => 'class'],
	'JFeedParserNamespace' => ['name' => '\\Joomla\\CMS\\Feed\\Parser\\NamespaceParserInterface', 'type' => 'interface'],
	'JFeedParserRss'       => ['name' => '\\Joomla\\CMS\\Feed\\Parser\\RssParser', 'type' => 'class'],
	'JFeedParserRssItunes' => ['name' => '\\Joomla\\CMS\\Feed\\Parser\\Rss\\ItunesRssParser', 'type' => 'class'],
	'JFeedParserRssMedia'  => ['name' => '\\Joomla\\CMS\\Feed\\Parser\\Rss\\MediaRssParser', 'type' => 'class'],

	'JImage'                     => ['name' => '\\Joomla\\CMS\\Image\\Image', 'type' => 'class'],
	'JImageFilter'               => ['name' => '\\Joomla\\CMS\\Image\\ImageFilter', 'type' => 'class'],
	'JImageFilterBackgroundfill' => ['name' => '\\Joomla\\Image\\Filter\\Backgroundfill', 'type' => 'class'],
	'JImageFilterBrightness'     => ['name' => '\\Joomla\\Image\\Filter\\Brightness', 'type' => 'class'],
	'JImageFilterContrast'       => ['name' => '\\Joomla\\Image\\Filter\\Contrast', 'type' => 'class'],
	'JImageFilterEdgedetect'     => ['name' => '\\Joomla\\Image\\Filter\\Edgedetect', 'type' => 'class'],
	'JImageFilterEmboss'         => ['name' => '\\Joomla\\Image\\Filter\\Emboss', 'type' => 'class'],
	'JImageFilterNegate'         => ['name' => '\\Joomla\\Image\\Filter\\Negate', 'type' => 'class'],
	'JImageFilterSketchy'        => ['name' => '\\Joomla\\Image\\Filter\\Sketchy', 'type' => 'class'],
	'JImageFilterSmooth'         => ['name' => '\\Joomla\\Image\\Filter\\Smooth', 'type' => 'class'],

	'JObject' => ['name' => '\\Joomla\\CMS\\Object\\CMSObject', 'type' => 'class'],

	'JExtensionHelper' => ['name' => '\\Joomla\\CMS\\Extension\\ExtensionHelper', 'type' => 'class'],

	'JHtml' => ['name' => '\\Joomla\\CMS\\HTML\\HTMLHelper', 'type' => 'class'],
];
