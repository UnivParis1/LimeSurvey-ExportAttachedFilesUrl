<?php
/**
 * E Plugin for LimeSurvey
 * Export code and complete answer in CSV
 *
 * @author Etienne Bohm <Etienne.Bohm@univ-paris1.fr>
 * @copyright 2016 Etienne Bohm <http://univ-paris1.fr>
 * @license AGPL v3
 * @version 0.1
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Affero Public License for more details.
 *
 */
class exportAttachedFilesUrl extends \ls\pluginmanager\PluginBase {
    protected $storage = 'DbStorage';
    static protected $name = 'Links attached export results';
    static protected $description = 'Include an url in the export of the csv results if there are attached files in the survey';


    public function __construct(PluginManager $manager, $id) {
        parent::__construct($manager, $id);

        $this->subscribe('newSurveySettings');
        $this->subscribe('beforeSurveySettings');
        $this->subscribe('listExportPlugins');
        $this->subscribe('listExportOptions');
        $this->subscribe('newExport');
    }

/* Mandatory implementation why I don't know */
    public function newSurveySettings() {
        $event = $this->getEvent();
        foreach ($event->get('settings') as $name => $value) {
            $this->set($name, $value, 'Survey', $event->get('survey'));
        }
    }
    
    public function beforeSurveySettings() {
        $event = $this->getEvent();
        $event->set("surveysettings.{$this->id}", array(
            'name' => get_class($this),
            'settings' => array(
                'title' => array(
                    'type' => 'info',
                    'content' => '<legend><small>Export Attached Files Url</small></legend>'
                ),
                'ExportAttachedFilesUrlBool' => array(
                    'type' => 'select',
                    'options' => array(
                        0 => 'No',
                        1 => 'Yes'
                    ),
                    'label' => 'Activate the export url for this survey',
                    'current' => $this->get(
                            'ExportAttachedFilesUrlBool', 'Survey', $event->get('survey')
                    ),
                ),
                'ExportAttachedFilesUrlBase' => array(
                    'type' => 'string',
                    'label' => "What's the base url for this survey",
                    'current' => $this->get('ExportAttachedFilesUrlBase', 'Survey', $event->get('survey'), null)
                ),
            )
        ));
    }

    /**
    * Registers this export type
    */
    public function listExportPlugins()
    {
        $surveyid = $_GET['surveyid'];

        $event = $this->getEvent();
        $ExportAttachedFilesUrlBool = $this->get('ExportAttachedFilesUrlBool', 'Survey', $surveyid, $event->get('surveyId'));

        if ($ExportAttachedFilesUrlBool == "1") {
            $event = $this->getEvent();
            $exports = $event->get('exportplugins');

            unset($exports['csv']);

            $newExport=array('csv'=>get_class());
            $exports=$newExport+$exports;
            $event->set('exportplugins', $exports);
        }
        else {
            $this->unsubscribe('newExport');
        }
    }

    public function listExportOptions()
    {
        $surveyid = $_GET['surveyid'];

        $event = $this->getEvent();
        $ExportAttachedFilesUrlBool = $this->get('ExportAttachedFilesUrlBool', 'Survey', $surveyid, $event->get('surveyId'));

        if ($ExportAttachedFilesUrlBool == '1') {
            $event->set('label',$this->get('title',null,null,'CSV'));
            if($this->get('default',null,null,$this->settings['default']['default'])) {
                $event->set('default', true);
            }
        }
    }

    public function newExport()
    {
        $surveyid = $_GET['surveyid'];
        $event = $this->getEvent();

        $ExportAttachedFilesUrlBool = $this->get('ExportAttachedFilesUrlBool', 'Survey', $surveyid, $event->get('surveyId'));

        $ExportAttachedFilesUrlBase = $this->get('ExportAttachedFilesUrlBase', 'Survey', $surveyid, $event->get('surveyId'));

        if ($ExportAttachedFilesUrlBool == "0") {
            throw new CHttpException(500, 'Error Check survey extension settings');
        }

        $writer = new exportAttachedFilesUrlWriter($ExportAttachedFilesUrlBase);
        
        $event->set('writer', $writer);
    }
}
