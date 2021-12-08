<?php

#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~#
#   MAGNIFICA WEB SCRIPTS - ANIMA GALLERY 2.5 - HTTP://DG.NO.SAPO.PT   #
#                 LICENSE: FREE FOR NON COMMERCIAL USE                 #
#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~#
#         NOTE: Do not change the space after the comma!               #
#  For consistency always use the web interface to modify this file    #
#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~#

/**********************************************************************
  Password for Admin Board
***********************************************************************/

define('DES_PASS', 'dcea978f2c1');


/**********************************************************************
  Index Page Info
***********************************************************************/
define('TITLE', 'Your title here');
define('SUFIXTITLE', '');
define('META_DESC', '');
define('META_KEYS', '');
define('DEFAULT_THEME', 'default');
define('DEFAULT_LANG', 'english');
define('THEME_LOCKED', 0);
define('LANG_LOCKED', 0);


/**********************************************************************
  Image Access
***********************************************************************/

# 0 - Thumbnails link to image through script to prevent batch downloads based on thumbnail link list
# 1 - Thumbnails link directly to full image
# 2 - Thumbnails only
# 3 - Thumbnails link to Preview page (The comment icon won't be displayed)
define('THUMBNAIL_LINK', 3);

# 0 - Preview links to image through script
# 1 - Preview links directly to full image
# 2 - Preview only
define('PREVIEW_LINK', 1);

# Size (pixels) for the preview image
define('PREVIEW_SIZE', 350);

/**********************************************************************
  Directory tree handling
***********************************************************************/

define('DEFAULT_TREE_DEPTH', 0);


/**********************************************************************
  Max character length for menu directory names (0 - Disable)
***********************************************************************/
define('DCROP', 25);


/**********************************************************************
  Max character length for image names in thumbnail footer
***********************************************************************/
define('FCROP', 25);


/**********************************************************************
  Max word length for image titles or LOG comments
***********************************************************************/
define('WCROP', 25);


/**********************************************************************
  Settings for thumbnail creation
***********************************************************************/
define('THUMBNAIL_MAX_DIM', 120);		// pixels
define('THUMBNAIL_QUALITY', 90);		// thumbnail quality percentage


/**********************************************************************
  Modules	0 - Disable		1 - Enable
***********************************************************************/
# User Preferences
define('USER_OPTIONS', 1);

# Social Bookmarks
define('SOCIAL_BOOKMARKS', 1);

# Last Web Uploads (Feed by HTTP Upload Log)
define('LAST_WEB_UPLOADS', 1);

# Blog
define('BLOG', 1);
define('BLOG_PER_PAGE', 5);


/**********************************************************************
  Gallery Sorting Options
***********************************************************************/
define('SORT_CLASS', 2);			// 1 - Filename, 2 - Image modification date, 3 - Upload Date
define('SORT_METHOD', 'SORT_REGULAR');	// SORT_REGULAR,SORT_NUMERIC or SORT_STRING
define('SORT_TYPE', 'SORT_DESC');		// SORT_ASC or SORT_DESC


/**********************************************************************
  Gallery Search
***********************************************************************/
define('SEARCH_ENABLED', 1);			// Enables search
define('SEARCH_ITEMS_PPAGE', 30);		// Number of images to display at once
define('SEARCH_KEY_LENGTH_MIN', 2);		// Minimum search key length (characters)


/**********************************************************************
  Thumbnail Footer Elements
combination of
LAST_MD_DATE, FILESIZE, DIMENSIONS, FILENAME, TITLE or HITS
(Separated by commas or spaces, commas parse a new line, first to last order)
FILENAME is only displayed if there's at least one direct link (Thumbnail or Preview)
***********************************************************************/
define('THUMBFOOTER', 'TITLE,DIMENSIONS FILESIZE');


/**********************************************************************
  Gallery images per page & # of colums
***********************************************************************/
define('IMG_PER_PAGE', 15);
define('GAL_COL', 3);


/**********************************************************************
  Comments
***********************************************************************/
define('GAL_COMMENTS', 1);
define('GAL_COM_MAX_LENGTH', 500);	// Max comment length (Characters)
define('GAL_COM_MIN_LENGTH', 10);	// Min comment length (Characters)
define('ANTI_SPAM_DELAY', 10);		// Time in minutes to prevent spam
define('GAL_COM_LOG_DSP', 200);	// Max characters to display in Recent Comment Log


/**********************************************************************
  Image ratings (0 - Disable 1 - Enable)
***********************************************************************/
define('GAL_RATINGS', 1);
define('GAL_RATE_MAX', 5);	// Max rating value
define('GAL_RATE_MIN', 1);	// Min rating value


/**********************************************************************
  Date Format/Offset
***********************************************************************/
define('DATE_FORMAT', 'H:i d-M-Y');			// Date format, for key reference consult http://www.php.net/date
define('DATE_OFFSET', 0);				// Default date offset from GMT+0, can be positive or negative, use "." as decimal separator (e.g.: 3.5), do not use "+" signs


/**********************************************************************
  WEB uploads (0 - Disable 1 - Enable)
***********************************************************************/
define('IMG_UPLOADS', 1);
define('UPL_MAX_FILESIZE', 1024);	// KB - Max filesize of uploaded images
define('ALLOW_OVERWRITE', 0);		// Allow existing image overwrite (If you set this on, its recommended to hide full paths)
define('REQUIRE_TITLE', 0);		// 0 - Title is optional  1 - Title is required
define('TITLE_MAX_LENGTH', 160);	// Max title length (Characters)
define('TITLE_MIN_LENGTH', 10);	// Max title length (Characters)
define('TITLE_FROM_FILE', 0);		// Generates titles from filenames in FTP uploads
define('TITLE_FROM_FILE_CHAR', '_');


/**********************************************************************
  Permissions   0 - All  1 - Admin Only
***********************************************************************/
define('PER_UPLOAD', 1);
define('PER_OVERWRITE', 1);

define('PER_BLOG', 1);
define('PER_BLOG_EDIT', 1);
define('PER_BLOG_DEL', 1);

define('PER_BLOG_COM', 0);
define('PER_BLOG_COM_DEL', 1);

define('PER_COM', 0);
define('PER_COM_DEL', 1);

define('PER_RATE', 0);


/**********************************************************************
  Logs
***********************************************************************/
define('LAST_UPLOADS_N', 6);
define('RECENT_COM_N', 5);
define('MOST_COM_N', 8);
define('MOST_VIEW_N', 8);
define('TOP_RATED_N', 5);
define('RECENT_COM', 1);
define('MOST_COM', 1);
define('MOST_VIEW', 1);
define('TOP_RATED', 1);


/**********************************************************************
  CAPTCHA / Anti-Spam
***********************************************************************/
define('CAPTCHA_BLOG', 1);
define('CAPTCHA_COM', 1);
define('CAPTCHA_UPL', 1);
define('CAPTCHA_LOG', 0);
define('RECAPTCHA_PUBK', '');
define('RECAPTCHA_PRIVK', '');

define('HUMANSPAM_NOURLS', '0');
define('HUMANSPAM_NOWORDS', '');
define('HUMANSPAM_IPBAN', '');


/**********************************************************************
  Performance
***********************************************************************/
define('SEARCH_LIMIT', 250);


/**********************************************************************
  Directory for storing image's data files and logs (information related to each image), this directory must exist and be writeable, default', "./thumb/", MUST END WITH "/"
***********************************************************************/
define('DATA_FILES_DIR', './thumb/');	


/**********************************************************************
  Albums directory: relative to Anima Gallery root ex.: "./albums/", MUST END WITH "/"
***********************************************************************/
define('ALB_DIR', './albums/');		

?>