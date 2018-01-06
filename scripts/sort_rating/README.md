Directory contains scripts to recalculate lib_clips.sort_rating field accordint to FSEARCH-1199 ticket.

lib_clips: sort rating will be a sum of the following values:
Admin Likes (sum from lib_clip_rating: admin_rating x  lib_settings: adminrating)
Registered User Likes (sum from lib_clips_rating: user_rating x lib_settings: registeredUser)
Non-Registered User Likes (sum from lib_clips_rating: ip_rating x lib_settings: ipRating)
Orders (sum from lib_order_items x lib_settings: purchase)
Clipbins (sum from lib_lb_items x lib_settings: clipbinsRating)
Q1 Views (sum from lib_clips_extra_statistic?? x lib_settings: viewsRating_q1 = new value)
Q2 Views (sum from lib_clips: viewed x lib_settings: viewsRating_q2 = new value)
Q2 Downloads (sum from lib_clips: downloaded x lib_settings: previewDownload)
Master Downloads (sum from lib_preview_download_statistics x lib_settings: purchase)
Format Rating (sort_format x lib_settings: formatRating)
Age Rating (sort_age x lib_settings: newContent)

BASE ALGO:
1) store intermediate values listed above in separate table (this is current state values table
2) every cron job:
    - check values in intermediate table with real-time values
    - update intermediate values for values which was changed in real time tables
    - set need_update flag to true
    - recalculate lib_clips.sort_rating value for rows with need_update flag == true
    - set need_update flag to false after update

HOW TO USE:
run: php ./executor.php