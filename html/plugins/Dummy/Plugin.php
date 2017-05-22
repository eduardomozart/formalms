<?php
namespace Plugin\Dummy;
defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                           |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */


class Plugin extends \FormaPlugin {
    public function install() {

        // addRequest is used to attach DummyAlmsController to the request r=alms/dummy/XXX
        self::addRequest("alms", "dummy", "DummyAlmsController", "DummyAlms");

        // addSetting is used to add a new setting in forma.lms
        parent::addSetting('dummy.foo', 'string', 255);
    }
}