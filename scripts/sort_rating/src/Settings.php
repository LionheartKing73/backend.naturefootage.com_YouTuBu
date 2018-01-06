<?php
namespace SortRating;

use SortRating\Traits\ConstantsAll;

/**
 * lib_settings values for synchronisation
 *
 * Class Settings
 * @package SortRating
 * @author nikita.bunenkov
 */
abstract class Settings
{
    use ConstantsAll;

    const ADMIN_RATING = 'adminrating';

    const REGISTERED_USER = 'registeredUser';

    const GUEST_USER = 'ipRating';

    const PURCHASE = 'purchase';

    const CLIPBIN_RATING = 'clipbinsRating';

    const VIEWS_Q1 = 'viewsRating_q1';

    const VIEWS_Q2 = 'viewsRating_q2';

    const PREVIEW_DOWNLOAD = 'previewDownload';

    const AGE_RATING_MORE_THAN_YEAR = 'age_rating_more_than_year';

    const AGE_RATING_MORE_THAN_HALF_YEAR = 'age_rating_more_than_half_year';

    const AGE_RATING_MORE_THAN_MONTH = 'age_rating_more_than_month';

    const AGE_RATING_MORE_THAN_WEEK = 'age_rating_more_than_week';

    const AGE_RATING_LESS_THAN_WEEK = 'age_rating_less_than_week';

    const FORMAT_RATING_ULTRA_HD = 'format_rating_ultra_hd';

    const FORMAT_RATING_HD = 'format_rating_hd';

    const FORMAT_RATING_SD = 'format_rating_sd';

    const GOLD_PRICE_RATING = 'gold_price_rating';
}