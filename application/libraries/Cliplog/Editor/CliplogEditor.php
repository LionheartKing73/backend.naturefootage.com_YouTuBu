<?php

namespace Libraries\Cliplog\Editor;

/** @noinspection PhpIncludeInspection */
require_once(APPPATH . 'libraries/CodeIgniterAccessors.php');
require_once(APPPATH . 'libraries/Cliplog/Editor/CliplogEditorAccessors.php');
require_once(APPPATH . 'libraries/Cliplog/Editor/CliplogEditorRequest.php');
require_once(APPPATH . 'libraries/Cliplog/Editor/Logging/LoggingTemplate.php');
require_once(APPPATH . 'libraries/Cliplog/Editor/Metadata/MetadataTemplate.php');
require_once(APPPATH . 'libraries/SorlSearchAdapter.php');

use Libraries\Accessors\CodeIgniterAccessors;

class CliplogEditor
{

    use CodeIgniterAccessors;
    use CliplogEditorAccessors;

    protected $templateData = array();

    function __construct()
    {
        $this->codeIgniterAccessorsInit();
    }

    public function pushTemplateData($arrayData)
    {
        if ($arrayData && is_array($arrayData)) {
            $this->templateData += $arrayData;
        }
    }

    public function replaceTemplateData($arrayData)
    {
        if ($arrayData && is_array($arrayData)) {
            foreach ($arrayData as $name => $value) {
                $this->templateData[$name] = $value;
            }
        }
    }

    public function getTemplateData()
    {
        return $this->templateData;
    }

    public function saveFormDataToClips($clipsIds, $formData, $optimize = false)
    {
        /** @var \clips_model $clipsModel */
        $clipsModel = $this->getModel('clips_model');
        if (!is_array($clipsIds)) {
            $clipsIds = array($clipsIds);
            $checkData = '0';
        } else {
            $checkData = '1';
        }
        foreach ($clipsIds as $clipId) {
            $clipsModel->save_clip_data($clipId, $formData, '', $checkData);
        }
        $clipsModel->add_to_index($clipsIds, $optimize);
    }

    public function getClipSavedMetadata($clipId)
    {
        $cliplogMetadata = $this->getCliplogEditorMetadataTemplate();
        $metadata = $cliplogMetadata->getEmptyTemplate();
        if ($clipId) {
            /** @var \clips_model $clipsModel */
            $clipsModel = $this->getModel('clips_model');
            //  $clipKeywords = $clipsModel->get_clip_keywords( $clipId );
            $clipData = $clipsModel->get_clip_for_edit($clipId);
            $metadata = $cliplogMetadata->rebuildClipToFormData($clipData);
            $metadata['sections_values'] = $metadata;
            $keywordsArray = array();
            // foreach ( $clipKeywords as $keyword ) {
            //     if ( isset( $keyword[ 'id' ] ) ) {
            //          $keywordsArray[ $keyword[ 'id' ] ] = $keyword[ 'id' ];
            //    }
            //  }
            // $metadata[ 'keywords' ] = $keywordsArray;
            $addCollectionsList = $clipsModel->get_clip_add_collections($clipId);
            if ($addCollectionsList && is_array($addCollectionsList)) {
                foreach ($addCollectionsList as $addCollection) {
                    if (isset($addCollection['id'])) {
                        $metadata['add_collection'][] = $addCollection['id'];
                        $metadata['sections_values']['add_collection'][] = $addCollection['id'];
                    }
                }
            } else {
                $metadata['add_collection'] = array();
                $metadata['sections_values']['add_collection'] = array();
            }
        }
        return $metadata;
    }

    public function pushSelectedClipIdToTemplateData($clipId)
    {
        $this->replaceTemplateData(
            array('selected_clip' => $clipId)
        );
    }

    public function pushEditorClipsIdsToTemplateData($clipsIds)
    {
        if ($clipsIds) {
            $idsString = implode(',', $clipsIds);
            $this->replaceTemplateData(
                array('clips_ids' => $idsString)
            );
        }
    }

    public function getCarouselClipsIds($selectedClipId, $userId, $filters = false)
    {
        /** @var \clips_model $clipsModel */
        $clipsModel = $this->getModel('clips_model');
        $carouselClipsIds = array();
        if (empty($filters) && empty($_SESSION['cliplog_search_filter_words'])) {
            $nextClipsIds = $clipsModel->getNextClipsIds($selectedClipId, 25, $userId);
            $prevClipsCount = 50 - count($nextClipsIds);
            $prevClipsIds = $clipsModel->getPrevClipsIds($selectedClipId, $prevClipsCount, $userId);
            foreach ($prevClipsIds as $clip) {
                $carouselClipsIds[] = $clip['id'];
            }
            $carouselClipsIds[] = $selectedClipId;
            foreach ($nextClipsIds as $clip) {
                $carouselClipsIds[] = $clip['id'];
            }
            return $carouselClipsIds;
        } elseif (isset($filters['submission_id'])) {
            $submissionModel = $this->getModel('submissions_model');
            $submission = $submissionModel->get_submission($filters['submission_id']);
            $clips = $clipsModel->get_clipsIds_by_codeMask($submission['code']);
        } elseif (isset($filters['backend_clipbin_id'])) {
            $clips = $clipsModel->get_clipIds_by_BackclipbinId($filters['backend_clipbin_id']);
        } else {
            $this->search_adapter = new \SorlSearchAdapter();//SorlSearchAdapter();
            if ($this->search_adapter->ping()) {
                unset($filters['backend_clipbin_id']);
                unset($filters['submission_id']);
                if (!empty($filters['wordsin'])) $filters['words'] = $filters['words'] . ' ' . $filters['wordsin'];
                $filters['brand_id'] = $filters['brand'];
                $filters['collection'] = $filters['collection_id'];
                if ($_SESSION['cliplog_search_filter_words']) $filters['words'] = (empty($filters['words'])) ? $_SESSION['cliplog_search_filter_words'] : $filters['words'];
                $clips = $this->search_adapter->search_clips($filters, 0, 50, array(), '', $this->group['is_admin']);
                return $clips['clips'];
            }
        }
        foreach ($clips as $clip) {
            $carouselClipsIds[] = $clip['id'];
        }
        //var_dump($clipsModel->getNextClipsIds( $selectedClipId, 25, $userId ));
        //var_dump($carouselClipsIds); die();
        return $carouselClipsIds;
    }

    public function getFutureNextClipId()
    {
        $editorRequest = $this->getCliplogEditorRequest();
        $editorClipsIds = $editorRequest->getClipsIds();
        $selectedClipId = $editorRequest->getSelectedClipId();
        $nextClipId = NULL;
        if ($selectedClipId) {
            if ($editorClipsIds) {
                # Ищем следующий Id в редактируемом списке
                if (is_array($editorClipsIds)) {
                    foreach ($editorClipsIds as $clipId) {
                        if ($clipId < $selectedClipId) {
                            $nextClipId = $clipId;
                            break;
                        }
                    }
                }
            } else {
                # Ищем следующий Id в общем списке
                /** @var \clips_model $clipsModel */
                $clipsModel = $this->getModel('clips_model');
                $clipData = $clipsModel->getPrevClipsIds($selectedClipId, 1);
                $clipData = array_shift($clipData);
                if (isset($clipData['id']) && $clipData['id']) {
                    $nextClipId = $clipData['id'];
                }
            }
        }
        return $nextClipId;
    }

    public function setGotoNextStatus($status)
    {
        $this->setSessionValue('gotoNextClip', $status);
    }

    public function getGotoNextStatus()
    {
        return $this->getSessionValue('gotoNextClip');
    }

}