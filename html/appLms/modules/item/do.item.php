<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

require_once _base_ . '/lib/lib.download.php';

function env_play($lobj, $options)
{
    list($file) = sql_fetch_row(sql_query('SELECT path'
        . ' FROM %lms_materials_lesson'
        . ' WHERE idLesson = ' . (int) $lobj->id . ''));

    if (!$file) {
        Util::jump_to($lobj->back_url);
    }

    $id_param = $lobj->getIdParam();

    if ($lobj->id_reference != false) {
        require_once _lms_ . '/class.module/track.item.php';
        $ti = new Track_Item($lobj, Docebo::user()->getIdSt()); // need id_resource, id_reference, type and environment
        $ti->setDate(date('Y-m-d H:i:s'));
        $ti->status = 'completed';
        $ti->update();
    }
    Util::download('/appLms/' . FormaLms\lib\Get::sett('pathlesson'), $file);
}

function play($idResource, $idParams, $back_url)
{
    //if(!checkPerm('view', true, 'organization') && !checkPerm('view', true, 'storage')) die("You can't access");
    //echo ("idResource = ".$idResource."; idParams = ".$idParams."; back_url = ".$back_url);
    list($file_title, $file) = sql_fetch_row(sql_query('SELECT title, path'
                                            . ' FROM %lms_materials_lesson'
                                            . " WHERE idLesson = '" . $idResource . "'"));

    //recognize mime type
    $expFileName = explode('.', $file);
    $totPart = count($expFileName) - 1;

    require_once _lms_ . '/lib/lib.param.php';
    $idReference = getLOParam($idParams, 'idReference');
    // NOTE: Track only if $idReference is present
    if ($idReference !== false) {
        require_once _lms_ . '/class.module/track.item.php';
        list($exist, $idTrack) = Track_Item::getIdTrack($idReference, getLogUserId(), $idResource, true);
        if ($exist) {
            $ti = new Track_Item((int) $idTrack, Docebo::user()->getIdSt());
            $ti->setDate(date('Y-m-d H:i:s'));
            $ti->status = 'completed';
            $ti->update($idReference,getLogUserId());
        } else {
            $ti = new Track_Item(false, Docebo::user()->getIdSt());
            $ti->createTrack($idReference, $idTrack, getLogUserId(), date('Y-m-d H:i:s'), 'completed', 'item');
        }
    }
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

    if ($session->get('direct_play') == 1) {
        if ($session->has('idCourse')) {
            TrackUser::closeSessionCourseTrack();

            $session->remove('idCourse');
            $session->remove('idEdition');
        }

        if ($session->has('test_assessment')) {
            $session->remove('test_assessment');
        }
        if ($session->has('cp_assessment_effect')) {
            $session->remove('cp_assessment_effect');
        }

        $session->set('current_main_menu', '1');
        $session->set('sel_module_id', '1');
        $session->set('is_ghost', false);
        $session->save();
    }

    if(strtolower($expFileName[$totPart]) === 'pdf') {
        $id_item = FormaLms\lib\Get::req('id_item', DOTY_INT, 0);

        require_once _base_ . '/lib/lib.utils.php';
        addJs('addons/pdfobject/', 'pdfobject.min.js');

        require_once Forma::inc(_base_ . '/lib/pdf/lib.pdf.php');
        $pdf = new PDF();
        $pdf_dimensions = $pdf->getPdfDimensions(_files_ . '/appLms/' . FormaLms\lib\Get::sett('pathlesson') . $file);

        $GLOBALS['page']->add('<div id="top" class="std_block">'
            . getBackUi(str_replace('&', '&amp;', $back_url), Lang::t('_BACK'))
            . '<div id="pdf-canvas"></div>'
            . '<script type="text/javascript">'
            . 'var options = {'
            . '    title: "' . $file_title . '",'
            . '    pdfOpenParams: { view: "Fit" },'
            . '    height: "' . (isset($pdf_dimensions["height"]) ? $pdf_dimensions["height"].'pt' : '100vh') . '"'
            . '};'
            . 'PDFObject.embed(".?modname=organization&op=custom_playitem&id_item=' . $id_item . '&embedded=1", "#pdf-canvas", options);'
            . '</script>'
            . '<br /><br />'
            . getBackUi(str_replace('&', '&amp;', $back_url), Lang::t('_BACK'))
            . '</div>', 'content');
    } else {
        //send file
        sendFile('/appLms/' . FormaLms\lib\Get::sett('pathlesson'), $file, $expFileName[$totPart]);
    }
}
