<?php

namespace Libraries\Cliplog\Editor\Metadata;

use Libraries\Cliplog\Editor\CliplogEditor;

class MetadataTemplate
{

    protected $cliplogEitor;

    protected $templateEmpty = array(
        'brand' => 0,
        'clip_description' => '',
        'clip_notes' => '',
        'date_filmed' => array(
            'month' => '',
            'year' => ''
        ),
        'license_type' => 0, # Rights Managed
        'price_level' => '',
        'releases' => '',
        'license_restrictions' => '',
        'audio_video' => '',
        'file_formats' => array(
            'camera_model' => '',
            'camera_chip_size' => '',
            'bit_depth' => '',
            'color_space' => '',
            'source_format' => '',
            'source_codec' => '',
            'source_frame_size' => '',
            'source_frame_rate' => '',
            'master_format' => '',
            'master_frame_size' => '',
            'master_frame_rate' => '',
            'digital_file_format' => '',
            'source_data_rate' => '',
            'digital_file_frame_size' => '',
            'digital_file_frame_rate' => '',
            'pricing_category' => '',
            'master_lab' => '',
        )
    );

    public function __construct(CliplogEditor $cliplogEditor)
    {
        $this->cliplogEitor = $cliplogEditor;
        $this->setClipToFormFieldProcessing();
    }

    public function getCliplogEditor()
    {
        return $this->cliplogEitor;
    }

    public function getEmptyTemplate()
    {
        $templateData = $this->templateEmpty;
        /** @var \collections_model $collectionsModel */
        $collectionsModel = $this->getCliplogEditor()->getModel('collections_model');
        $templateData['collection'] = $collectionsModel->getDefaultClipCollectionName();
        $templateData['sections_values'] = $templateData;
        return $templateData;
    }

    public function getTemplateData($templateId)
    {
        $metadata = array();
        if (!empty($templateId)) {
            /** @var \cliplog_templates_model $cliplogTemplatesModel */
            $cliplogTemplatesModel = $this->getCliplogEditor()->getModel('cliplog_templates_model');
            $templateData = $cliplogTemplatesModel->getMetadataTemplate($templateId);
            if ($templateData && isset($templateData[0]['json'])) {
                $metadata = json_decode($templateData[0]['json'], TRUE);
                $metadata['cliplog_keyword_set'] = $templateData;
            }
        } else {
            $metadata = $this->getEmptyTemplate();
        }
        return $metadata;
    }

    /**
     * @param array $metadata - current TemplateData
     * @param array $newKeywords - new Keywords array
     *
     * @return array
     */
    public function addKeywordsToTemplate($metadata, $newKeywords = null)
    {
        // Не раскоменчивать исчезают кейворды выбраные
        /*if ( $metadata && empty( $metadata[ 'cliplog_keyword_set_id' ] )) {
            $newmetadata = json_decode( $metadata[ 'cliplog_keyword_set' ][ 'json' ], TRUE );
            unset($newmetadata['keywords']);
            $newmetadata[ 'cliplog_keyword_set' ]['json'] = json_encode($newmetadata);
            return $newmetadata;
        }*/

        // ЕСЛИ НАДО добавлять к старым кейвордам новые
        if ($metadata && isset($metadata['cliplog_keyword_set']['json']) && !is_null($newKeywords) && !empty($newKeywords)) {
            $newmetadata = json_decode($metadata['cliplog_keyword_set']['json'], TRUE);
            foreach ($newKeywords as $keyword) {
                $newmetadata['keywords'][$keyword] = $keyword;
            }
            $newmetadata['cliplog_keyword_set']['json'] = json_encode($newmetadata);
            return $newmetadata;
        } else {
            return $metadata;
        }

        // Применяем только новый шаблон кейвордов
        return $metadata;
    }

    # @TODO Код, который ниже, нужно вынести в отдельные объект. Тут ему не место

    protected $clipToFormFieldRelations = array(
        # 'FormFieldName'  => 'ClipFieldName'
        #'collection' => 'collection',
        'brand' => 'brand',
        'clip_description' => 'description',
        'clip_notes' => 'notes',
        'date_filmed' => 'film_date', # <- Нужно преобразовать данные
        'license_type' => 'license',
        'price_level' => 'price_level',
        'license_restrictions' => 'license_restrictions',
        'audio_video' => 'audio_video',
        'releases' => 'releases',
        'file_formats' => array( # <- Нужно преобразовать данные
            'camera_model' => 'camera_model',
            'camera_chip_size' => 'camera_chip_size',
            'bit_depth' => 'bit_depth',
            'color_space' => 'color_space',
            'source_format' => 'source_format',
            'source_codec' => 'source_codec',
            'source_frame_size' => 'source_frame_size',
            'source_frame_rate' => 'source_frame_rate',
            'master_format' => 'master_format',
            'master_frame_size' => 'master_frame_size',
            'master_frame_rate' => 'master_frame_rate',
            'digital_file_format' => 'digital_file_format',
            'source_data_rate' => 'source_data_rate',
            'digital_file_frame_size' => 'digital_file_frame_size',
            'digital_file_frame_rate' => 'digital_file_frame_rate',
            'pricing_category' => 'pricing_category',
            'master_lab' => 'master_lab'
        )
    );

    protected $clipToFormFieldProcessing = array(# 'FormFieldName' => function () {}
    );

    protected function setClipToFormFieldProcessing()
    {
        $this->clipToFormFieldProcessing['date_filmed'] = function ($clipValue = NULL) {
            # На входе: 'YYYY-MM-DD'
            # На выходе: array ( 'month' => M, 'year' => YYYY )
            if ($clipValue && $clipValue != '0000-00-00') {
                $timestamp = strtotime($clipValue);
                return array(
                    'month' => date('n', $timestamp),
                    'year' => date('Y', $timestamp),
                );
            }
            return '';
        };
    }

    public function rebuildClipToFormData($clipData)
    {
        if ($clipData) {
            $resultData = array();
            # Получаем пустой шаблон как образец
            $emptyTemplate = $this->getEmptyTemplate();
            foreach ($emptyTemplate as $fieldName => $noUse) {
                # Узнаем, нужно ли изменить название поля
                if ($this->isNeedClipToFormRebuild($fieldName)) {
                    $clipFieldName = $this->getClipToFormFieldName($fieldName); # <- Тут может быть массив
                    if (is_array($clipFieldName)) {
                        # Сложные данные, массив
                        $resultData[$fieldName] = $this->rebuildClipFieldValues($clipData, $fieldName, $clipFieldName);
                    } else {
                        # Простые данные
                        $resultData[$fieldName] = $this->rebuildClipFieldValue($clipData, $fieldName, $clipFieldName);
                    }
                }
            }
            return $resultData;
        }
        return $clipData;
    }

    protected function rebuildClipFieldValues($clipData, $fieldName, $clipFieldsArray)
    {
        if ($fieldName && $clipFieldsArray) {
            $fieldValue = array();
            foreach ($clipFieldsArray as $formFieldName => $clipFieldName) {
                # Получаем значение поля клипа
                $currentValue = (isset($clipData[$clipFieldName])) ? $clipData[$clipFieldName] : '';
                # Узнаем, нужно ли изменить формат данных
                if ($this->isNeedClipToFormProccess($formFieldName, $fieldName)) {
                    # Меняем формат данных
                    $valueHandler = $this->getClipToFormProccessClosure($formFieldName, $fieldName);
                    $currentValue = $valueHandler($currentValue);
                }
                $fieldValue[$formFieldName] = $currentValue;
            }
            return $fieldValue;
        }
        return NULL;
    }

    protected function rebuildClipFieldValue($clipData, $fieldName, $clipFieldName)
    {
        if ($fieldName && $clipFieldName) {
            # Получаем значение поля клипа
            $fieldValue = (isset($clipData[$clipFieldName])) ? $clipData[$clipFieldName] : '';
            # Узнаем, нужно ли изменить формат данных
            if ($this->isNeedClipToFormProccess($fieldName)) {
                # Меняем формат данных
                $valueHandler = $this->getClipToFormProccessClosure($fieldName);
                $fieldValue = $valueHandler($fieldValue);
            }
            return $fieldValue;
        }
        return NULL;
    }

    protected function isNeedClipToFormRebuild($fieldName)
    {
        return isset($this->clipToFormFieldRelations[$fieldName]);
    }

    protected function getClipToFormFieldName($fieldName)
    {
        return $this->clipToFormFieldRelations[$fieldName];
    }

    protected function isNeedClipToFormProccess($fieldName, $parentFieldName = NULL)
    {
        if ($parentFieldName) {
            return isset($this->clipToFormFieldProcessing[$parentFieldName][$fieldName]);
        }
        return isset($this->clipToFormFieldProcessing[$fieldName]);
    }

    protected function getClipToFormProccessClosure($fieldName, $parentFieldName = NULL)
    {
        if ($parentFieldName) {
            return $this->clipToFormFieldProcessing[$parentFieldName][$fieldName];
        }
        return $this->clipToFormFieldProcessing[$fieldName];
    }

}