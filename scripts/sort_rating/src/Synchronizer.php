<?php

namespace SortRating;

use SortRating\Contracts\Runnable;
use SortRating\Traits\LibSettings;
use SortRating\Traits\PdoUser;
use PDO;
use PDOException;
use SortRating\Log\Factory as LogFactory;

/**
 * Class Synchronizer
 * @package SortRating
 * @author nikita.bunenkov
 */
class Synchronizer implements Runnable
{
    use PdoUser, LibSettings;

    private $_stateTable = '__sort_rating_state';

    private $_clipsTable = 'lib_clips';

    /**
     * number of rows in {$this->_clipsTable} to update per query
     * @var int
     */
    private $_updateLimit = 100;

    public function __construct(PDO $dbh)
    {
        $this->setPDO($dbh);
    }

    public function run()
    {
        try {
            $this->_getLibSettings();
            $this->_process();
        } catch (PDOException $ex) {
            // handle exception here
            LogFactory::instance()->exception($ex);
        }
    }


    /**
     * get array of settings keys to take values from db
     *
     * used in LibSettings trait
     * @see LibSettings
     *
     * @return array
     */
    protected function _settingsKeys()
    {
       return Settings::all();
    }

    /**
     * - update {$this->_clipsTable}.sort_rating
     * - update version value for {$this->_stateTable}
     * - set need_update flag to false
     * @return void
     */
    private function _process()
    {
        while($stateIds = $this->_rowsForUpdate()) {
            $this->getPDO()->beginTransaction();
            try {
                $this->_processClips($stateIds);
                $this->_processState($stateIds);
                $this->getPDO()->commit();
            } catch(PDOException $ex) {
                $this->getPDO()->rollBack();
                throw $ex;
            }
        }
    }

    /**
     * update {$this->_clipsTable}.sort_rating
     *
     * -- format_rating & age_rating used as is
     * -- gold_price is a flag, if 1 => add GOLD_PRICE_RATING to sort_rating, if 0 - not
     *
     * -- also update {$this->clipsTable}.sort_age value from {$this->_stateTable}.age_rating
     *
     * @param array $stateIds array of ids to use in update
     * @return void
     */
    private function _processClips(array $stateIds)
    {
        $statement = $this->getPDO()->prepare(
            "UPDATE {$this->_clipsTable} as clip
                JOIN {$this->_stateTable} as state ON clip.id = state.item_id
                SET clip.sort_rating = 
                      state.orders * {$this->_settings[Settings::PURCHASE]} 
                    + state.admin_likes * {$this->_settings[Settings::ADMIN_RATING]} 
                    + state.user_likes * {$this->_settings[Settings::REGISTERED_USER]} 
                    + state.guest_likes * {$this->_settings[Settings::GUEST_USER]} 
                    + state.clipbins * {$this->_settings[Settings::CLIPBIN_RATING]} 
                    + state.q1_views * {$this->_settings[Settings::VIEWS_Q1]} 
                    + state.q2_views * {$this->_settings[Settings::VIEWS_Q2]} 
                    + state.q2_downloads * {$this->_settings[Settings::PREVIEW_DOWNLOAD]} 
                    + state.format_rating 
                    + state.age_rating
                    + state.gold_price * {$this->_settings[Settings::GOLD_PRICE_RATING]}
                    + state.additional_sort_rating,
                    clip.sort_age = CASE state.age_rating
                        WHEN {$this->_settings[Settings::AGE_RATING_LESS_THAN_WEEK]} THEN 1
                        WHEN {$this->_settings[Settings::AGE_RATING_MORE_THAN_WEEK]} THEN 2
                        WHEN {$this->_settings[Settings::AGE_RATING_MORE_THAN_MONTH]} THEN 3
                        WHEN {$this->_settings[Settings::AGE_RATING_MORE_THAN_HALF_YEAR]} THEN 4
                        WHEN {$this->_settings[Settings::AGE_RATING_MORE_THAN_YEAR]} THEN 5
                        ELSE 0
                    END
                WHERE state.id IN ({$this->_prepareArray($stateIds)})"
        );
        
        $statement->execute();
    }

    /**
     * - update version value for {$this->_stateTable}
     * - set need_update flag to false for {$this->_stateTable}
     * @param array $stateIds array of ids to use in update
     * @return void
     */
    private function _processState(array $stateIds)
    {
        $statement = $this->getPDO()->prepare(
            "UPDATE {$this->_stateTable} SET version = version + 1, need_update = 0 
                WHERE id IN ({$this->_prepareArray($stateIds)})"
        );
        $statement->execute();
    }

    /**
     * get rows for update from {$this->_state} table
     * @return array of ids
     */
    private function _rowsForUpdate()
    {
        $statement = $this->getPDO()->prepare(
            "SELECT id FROM {$this->_stateTable} WHERE need_update = 1 LIMIT {$this->_updateLimit}"
        );

        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }
}