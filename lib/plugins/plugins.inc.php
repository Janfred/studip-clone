<?php
/**
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 */

// helper classes
require_once 'core/Permission.class.php';
require_once 'core/StudIPInstitute.class.php';
require_once 'core/StudIPUser.class.php';
require_once 'core/StudIPCore.class.php';

// plugin base class
require_once 'core/StudIPPlugin.class.php';

// plugin interfaces
require_once 'core/AdministrationPlugin.class.php';
require_once 'core/HomepagePlugin.class.php';
require_once 'core/PortalPlugin.class.php';
require_once 'core/StandardPlugin.class.php';
require_once 'core/StudienmodulManagementPlugin.class.php';
require_once 'core/SystemPlugin.class.php';

// old navigation classes (deprecated)
require_once 'core/StudipPluginNavigation.class.php';
require_once 'core/PluginNavigation.class.php';

// old plugin base classes (deprecated)
require_once 'core/AbstractStudIPLegacyPlugin.class.php';
require_once 'core/AbstractStudIPAdministrationPlugin.class.php';
require_once 'core/AbstractStudIPCorePlugin.class.php';
require_once 'core/AbstractStudIPHomepagePlugin.class.php';
require_once 'core/AbstractStudIPPortalPlugin.class.php';
require_once 'core/AbstractStudIPStandardPlugin.class.php';
require_once 'core/AbstractStudIPSystemPlugin.class.php';

// core plugin API
require_once 'core/Role.class.php';
require_once 'db/RolePersistence.class.php';
require_once 'engine/PluginEngine.class.php';
require_once 'engine/PluginNotFound.class.php';
require_once 'engine/StudIPTemplateEngine.class.php';
