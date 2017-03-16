<?php

/**
 * exportCompleteAnswersWriter part of exportCompleteAnswers Plugin for LimeSurvey
 * Writer for the plugin
 *
 * @author Etienne Bohm <Etienne.Bohm@univ-paris1.fr>
 * @copyright 2016-2017 Etienne Bohm <Etienne.Bohm@univ-paris1.fr>
 * @copyright 2016-2017 University Paris 1  <http://univ-paris1.fr>
 * @license AGPL v3
 * @version 0.9.1
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

Yii::import('application.helpers.admin.export.*');
class exportAttachedFilesUrlWriter extends CsvWriter {

    public function __construct($urlBase) {
        parent::__construct();

        $this->urlBase = $urlBase;
    }

    /**
    * Initialization method
    * Update $oOptions to use own, keep the old headingFormat in $this->oldHeadFormat
    *
    * @param Survey $oSurvey
    * @param mixed $sLanguageCode
    * @param FormattingOptions $oOptions
    */
    public function init(\SurveyObj $oSurvey, $sLanguageCode, \FormattingOptions $oOptions) {
        parent::init($oSurvey, $sLanguageCode, $oOptions);
    }

    protected function transformResponseValue($value, $fieldType, \FormattingOptions $oOptions, $column = null) {
        $valueField = parent::transformResponseValue($value, $fieldType, $oOptions, $column);

        if ( (($fieldType == "|") && (($jsonArray = json_decode($value, true)) != NULL) && (is_array($jsonArray)) ) == false) {
            return $valueField;
        }

        $i = 0;
        foreach ($jsonArray as $array) {
            if (isset($array['filename']) && strlen($array['filename']) > 0) {
                $jsonArray[$i]['fileurl'] = $this->urlBase . $array['filename'];
            }
            $i++;
        }

        $valueJson = json_encode($jsonArray, JSON_UNESCAPED_SLASHES);

        return $valueJson;
    }
}
