<?php
// Prevent direct access
if (count(get_included_files()) == 1) {
    exit('Direct access not allowed');
}

// SMS API Configuration
define('SMS_API_KEY', 'aac8d9570753ad1481fdd92e68f74db7-12573a69-b8b6-4954-ae29-6245f68cd555');
define('SMS_API_URL', 'https://51xlkj.api.infobip.com/sms/2/text/advanced');
define('SMS_SENDER_ID', '447491163443'); 