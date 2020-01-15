<?php
define("MIN_LENGTH_PASSWORD", 8);
define("MIN_LENGTH_NAME", 3);
define("MAX_LENGTH_NAME", 100);
//8 symbols, one letter and one number
define("PASSWORD_PATTERN", "^(?=.*[A-Za-z])(?=.*\d)[a-zA-Z0-9,.;\^!@#$%&*()+=:_'\s-]{8,}$^");
define("PASSWORD_WRONG_PATTERN_MESSAGE", 'Password must have 8 symbols containing at least one letter and one number.');
define("MAX_AMOUNT", 10000000);
define("NO_AVATAR_URL", 'avatars' . DIRECTORY_SEPARATOR . 'no-avatar.png');
define("CATEGORY_INCOME", 1);
define("CATEGORY_OUTCOME", 0);
define('TRANSFER_CATEGORY_ID', 19);
define("TOKEN_LENGTH", 30);
define("TOKEN_EXPIRATION_MINUTES", 30);
define("MSG_SUPPORTED_CURRENCIES", "Supported currencies are BGN, EUR and USD.");
define("MAX_DAYS_NOT_ACTIVE", 7);
define("IMAGE_MAX_UPLOAD_SIZE", 2097152); //2MB
$white_list_not_logged = [
    'login',
    'register',
    'sendEmail',
    'setNewPassword',
    'sendNotificationsToUnactiveUsers',
    'executePlannedPayments'
];
?>