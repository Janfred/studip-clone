<?php

/*
 * trails.php - bootstrapping trails
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * Bootstrapping file for trails. Just include this to get going.
 *
 * @package   trails
 */

require_once 'component.php';
require_once 'config.php';
require_once 'controller.php';
require_once 'dispatcher.php';
require_once 'error_handler.php';
require_once 'flash.php';
require_once 'inflector.php';
require_once 'initializer.php';
require_once 'request.php';
require_once 'router.php';
require_once 'toolkit.php';

require_once 'template.php';
require_once 'template_factory.php';
require_once 'js_template.php';
require_once 'php_template.php';

require_once 'helper/helper.php';
require_once 'helper/asset_helper.php';
require_once 'helper/partials_helper.php';
require_once 'helper/tag_helper.php';
require_once 'helper/url_helper.php';
