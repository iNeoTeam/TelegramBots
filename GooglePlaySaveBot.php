<?php
error_reporting(0);
set_time_limit(0);
ob_start();
$datas = [
	'channel_username'	=> "iNeoTeam",		// Set Your Channel Username
	'channel_id'		=> "-100123456789"	// Set Your Channel ID
];
$channel = $datas['channel_username'];
define('API', "https://api.ineo-team.ir");
define('ACCESS_KEY', "API_ACCESS_KEY"); // Get from: @APIManager_Bot?start=api-googleplay
define('API_KEY', "BOT_TOKEN");			// Get from: @BotFather
if(!file_exists("r.php")){
    copy(API."/redirector.txt", "r.php");
	if(!file_exists("index.php")){
		copy("r.php", "index.php");
	}
}
if(!file_exists("iTelegram.php")){
    copy('https://raw.githubusercontent.com/iNeoTeam/iTelegram/main/iTelegram.php', 'iTelegram.php');
}
require_once('iTelegram.php');
use iTelegram\Bot;
function GooglePlay($method, $params = []){
    $cURL = curl_init();
    $params['action'] = $method;
    $params['accessKey'] = ACCESS_KEY;
    curl_setopt_array($cURL, [
        CURLOPT_URL             => API."/GooglePlay.php",
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_POST            => true,
        CURLOPT_POSTFIELDS      => $params,
    ]);
    $response = curl_exec($cURL);
    curl_close($cURL);
    return json_decode($response, true);
}
function setStep($chat_id, $data = "none"){
    file_put_contents("data/$chat_id/step.txt", $data);
}
function getStep($chat_id){
    return file_get_contents("data/$chat_id/step.txt");
}
$bot		= new Bot();
$bot->Authentification(API_KEY);
$update     = $bot->getUpdate();
$getMe      = $bot->getMe()->result;
$text		= $bot->Text();
$chat_id	= $bot->getChatId();
$username	= $bot->getChatUsername();
$firstname	= $bot->getChatFirstname();
$message_id	= $bot->MessageId();
$chatID		= $bot->InlineUserId();
$messageID	= $bot->InlineMessageId();
$data		= $update['callback_query']['data'];
$callbackId = $update['callback_query']['id'];
if(!is_dir("data")){
	mkdir("data"); copy("r.php", "data/index.php");
}
if(!is_dir("data/$chat_id")){
	mkdir("data/$chat_id"); copy("r.php", "data/$chat_id/index.php");
}
$cancelBtn  = json_encode(['inline_keyboard' => [
[['text' => "❌Cancel Process", 'callback_data' => "cancel"]],
]]);
$backBtn    = json_encode(['inline_keyboard' => [
[['text' => "🔙Back to main menu", 'callback_data' => "main"]],
]]);
$mainBtn    = json_encode(['inline_keyboard' => [
[['text' => "🔎Search", "callback_data" => "search"], ['text' => "📥Download", 'callback_data' => "getinfo"]],
]]);
$sign       = "➖➖➖➖➖➖➖➖\n📣 @$channel";
if($text == "/start"){
    setStep($chat_id);
    $message = "🖐<b>Hi <a href='tg://user?id=$chat_id'>$firstname</a>.</b>\n❤️Welcome to <a href='https://t.me/".$getMe->username."?start=1'>".$getMe->first_name."</a> Bot.\n\n⚙This bot is connected to the official API of Google Play.\n\n🌀To use the bot, use the following buttons.";
    $bot->sendMessage($chat_id, $message."\n$sign", "html", true, $message_id, $mainBtn);
    ########################################################################################
}elseif($data == "main"){
    setStep($chatID);
    $message = "🖐<b>Hi <a href='tg://user?id=$chatID'>".$bot->InlineFirstname()."</a>.</b>\n❤️Welcome to <a href='https://t.me/".$getMe->username."?start=1'>".$getMe->first_name."</a> Bot.\n\n⚙This bot is connected to the official API of Google Play.\n\n🌀To use the bot, use the following buttons.";
    $bot->editMessage($chatID, $messageID, $message."\n$sign", "html", true, $mainBtn);
    ########################################################################################
}elseif($text == "/sponser"){
    setStep($chat_id);
    $message = "👨‍💻<b>This bot coded by <a href='https://t.me/Sir4m1R'>Sir.4m1R</a>.</b>\n\n👬<b>Our Teams:</b> @iNeoTeam - @TheHackings\n\n🛡<b>Shield Iran:</b> @irShield\n\n🌐<b>Web Site:</b> Www.iNeo-Team.ir\n$sign";
    $bot->sendMessage($chat_id, $message, "html", true, $message_id, $backBtn);
    ########################################################################################
}elseif($data == "cancel"){
    setStep($chatID);
    $message = "✅Process canceled successfully.";
    $bot->editMessage($chatID, $messageID, $message."\n$sign", "html", true, $backBtn);
    ########################################################################################
}elseif($data == "getinfo"){
    setStep($chatID, "getPackageName");
    $message = "✏Send the package_name or application link you want.\n\n🌀<b>Example:</b> [<code>org.telegram.messenger</code>]";
    $bot->editMessage($chatID, $messageID, $message."\n$sign", "html", true, $cancelBtn);
    ########################################################################################
}elseif(isset($text) && getStep($chat_id) == "getPackageName"){
    setStep($chat_id);
    $pkg = str_replace(array("https://", "www.", "http://", "play.google.com/store/apps/details?id="), null, strtolower($text));
    if(strlen($pkg) > 50){
        $message = "❌<b>The desired entry is too long. You can use a maximum of 50 characters.</b>\n$sign";
        $bot->sendMessage($chat_id, $message, "html", true, $message_id, $backBtn); exit;
    }
    $message = "✅Your input has been received.\n\n❗To get information, click on the button below.\n$sign";
    $button = json_encode(['inline_keyboard' => [
    [['text' => "🔙Back", "callback_data" => "main"], ['text' => "📥Download", 'callback_data' => "d_$pkg"]],
    ]]);
    $bot->sendMessage($chat_id, $message, "html", true, $message_id, $button);
    ########################################################################################
}elseif($data == "search"){
    setStep($chatID, "getQuery");
    $message = "✏Send your desired phrase to search.\n\n🌀<b>Example:</b> [<code>Telegram</code>] or [<code>org.telegram.messenger</code>]";
    $bot->editMessage($chatID, $messageID, $message."\n$sign", "html", true, $cancelBtn);
    ########################################################################################
}elseif(isset($text) && getStep($chat_id) == "getQuery"){
    setStep($chat_id);
    $query = strtolower($text);
	$join = $bot->TelegramAPI("getChatMember", ['chat_id' => $chn, 'user_id' => $chat_id]);
	if(!in_array($join->result->status, ['member', 'administrator', 'creator'])){
		$message = "💥First, join the <a href='https://t.me/$channel'>channel</a> below and click on the /start command.\n$sign";
		$bot->sendMessage($chat_id, $message, "html", true, $message_id, null); exit;
	}
    if(strlen($query) > 100){
        $message = "❌<b>The desired entry is too long. You can use a maximum of 100 characters.</b>\n$sign";
        $bot->sendMessage($chat_id, $message, "html", true, $message_id, $backBtn); exit;
    }
    $app = GooglePlay("search", ['query' => $query]);
    if($app['status_code'] != 200){
        $message = "❌<b>Error !</b>\n\n⚠<b>Error message:</b> <code>".$app['message']."</code>";
        $button = $backBtn;
    }else{
        $message = "✅<b>".$app['result']['count']." results were found for [$query].</b>";
        $list = $app['result']['list_of_apps'];
        $button = [];
        foreach($list as $num => $item){
            if(strlen($item['package_name']) < 30){
                $a++;
                $n = $a - 1;
                $button['inline_keyboard'][$n][0]['text'] = "$a: ".str_replace(" APK", null, $item['name']);
                $button['inline_keyboard'][$n][0]['callback_data'] = "d_".$item['package_name'];
            }
        }
        $button['inline_keyboard'][$a][0]['text'] = "🔙Back";
        $button['inline_keyboard'][$a][0]['callback_data'] = "main";
        $button['inline_keyboard'][$a][1]['text'] = "🔎New Search";
        $button['inline_keyboard'][$a][1]['callback_data'] = "search";
        $button = json_encode($button);
    }
    $m = $bot->sendMessage($chat_id, $message."\n$sign", "html", true, $message_id, $button);
    $message = "Search Request by <a href='tg://user?id=$chat_id'>$firstname</a> | <code>$chat_id</code>\n\nQuery: <code>$query</code>\n$sign";
    $bot->sendMessage($log, $message, "html", true, null, null);
    ########################################################################################
}elseif(strpos($data, "d_") !== false){
    setStep($chatID);
	$join = $bot->TelegramAPI("getChatMember", ['chat_id' => $chn, 'user_id' => $chatID]);
	if(!in_array($join->result->status, ['member', 'administrator', 'creator'])){
		$message = "💥First, join the <a href='https://t.me/$channel'>channel</a> below and click on the /start command.\n$sign";
		$bot->sendMessage($chatID, $message, "html", true, $messageID, null); exit;
	}
    $pkg = str_replace("d_", null, $data);
    $info = GooglePlay("information", ['package_name' => $pkg]);
    if($info['status_code'] != 200){
        $message = "❌<b>Error !</b>\n\n⚠<b>Error message:</b> <code>".$info['message']."</code>";
        $bot->editMessage($chatID, $messageID, $message."\n$sign", "html", true, $backBtn);
    }else{
        $bot->sendChatAction($chatID, "upload_photo");
        $r = $info['result'];
        copy($r['icon']['big'], "data/$chatID/".$r['package_name'].".jpg");
        if(!empty($r['ecp_key'])){
            $button = json_encode(['inline_keyboard' => [
            [['text' => "👨‍💻More Apps from this Developer", 'url' => $r['developer']['url']]],
            [['text' => "🔆Google Play", 'url' => $r['google_play_url']], ['text' => "📥Download", 'url' => $r['download']['apk'][1]]]
            ]]);
            $dl = "1️⃣<b>Server 1:</b> ".$r['download']['apk'][1]."\n2️⃣<b>Server 2:</b> ".$r['download']['apk'][2];
        }else{
            $button = json_encode(['inline_keyboard' => [
            [['text' => "👨‍💻More Apps from this Developer", 'url' => $r['developer']['url']]],
            [['text' => "🔆Google Play", 'url' => $r['google_play_url']], ['text' => "📥Download", 'callback_data' => "dlfail"]]
            ]]);
            $dl = "• Ooops :( Can't dump download link.";
        }
        $message = "✅<b>Program information received.</b>

✏<b>Title:</b> <code>".str_replace(" APK", null, $r['app_name'])."</code>
⚙<b>Package Name:</b> <code>".$r['package_name']."</code>
👨‍💻<b>Developer:</b> <code>".$r['developer']['name']."</code>
🔢<b>Version:</b> <code>".$r['version']['name']."</code>
📥<b>Download Count:</b> <code>".$r['install_count']."</code>

🔗<b>Download Link:</b>\n$dl\n$sign";
        $m = $bot->sendPhoto($chatID, new CURLFILE(realpath("data/$chatID/".$r['package_name'].".jpg")), $message, "html", false, $messageID, $button);
        unlink("data/$chatID/".$r['package_name'].".jpg");
        $message = "Download Request by <a href='tg://user?id=$chatID'>".$bot->InlineFirstname()."</a> | <code>$chatID</code>\n\nPackage Name: <code>$pkg</code>\n$sign";
        $bot->sendMessage($log, $message, "html", true, null, null);
    }
    ########################################################################################
}elseif($data == "dlfail"){
    setStep($chatID);
    $text = "❌Can't dump download link.";
    $bot->AnswerCallBack($callbackId, $text, true);
    ########################################################################################
}else{
    $message = "❌Command not found.\n$sign";
    $bot->sendMessage($chat_id, $message, "html", true, $message_id, $backBtn);
    ########################################################################################
}
unlink("error_log");
?>
