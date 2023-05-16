<?php
error_reporting(0);
set_time_limit(0);
ob_start();
$config = [
	'channel' => "iNeoTeam", # Your Telegram channel username without @.
	'api' => "https://api.ineo-team.ir", # Don't change it !
	'accessKey' => "API_ACCESS_KEY", # Get from T.me/APIManager_Bot?start=api-youtube .
	'botToken' => "TELEGRAM_BOT_HTTP_ACCESS_TOKEN" # Get from @BotFather .
];
#####################################################################################
if(!file_exists("iTelegram.php")){
    copy('https://raw.githubusercontent.com/iNeoTeam/iTelegram/main/iTelegram.php', 'iTelegram.php');
}
require_once('iTelegram.php');
use iTelegram\Bot;
#####################################################################################
function YouTube($url){
	global $config;
	$cURL = curl_init();
	$param = ['video_id' => $url, 'accessKey' => $config['accessKey']];
	curl_setopt($cURL, CURLOPT_URL, $config['api']."/youtube.php");
	curl_setopt($cURL, CURLOPT_POSTFIELDS, $param);
	curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
	$result = json_decode(curl_exec($cURL), true);
	curl_close($cURL);
	return $result;
}
function SizeConvert($size, $precision = 0){
    $types = array('B', 'KB', 'MB', 'GB', 'TB');
    $_ = log($size, 1024);
    $pow = pow(1024, $_ - floor($_));
    return round($pow, $precision)." ".$types[floor($_)];
}
#####################################################################################
$bot			= new Bot();
$bot->Authentification($config['botToken']);
$update			= $bot->getUpdate();
$text			= $bot->Text();
$chat_id		= $bot->getChatId();
$username		= $bot->getChatUsername();
$firstname		= $bot->getChatFirstname();
$message_id		= $bot->MessageId();
$chatID			= $bot->getInlineChatId();
$messageID		= $bot->InlineMessageId();
$data			= $update['callback_query']['data'];
$callback_id	= $update['callback_query']['id'];
$sign			= "➖➖➖➖➖➖➖➖\n📣 @".$config['channel'];
#####################################################################################
if($text == "/start"){
	$message = "🖐<b>Hello <a href='tg://user?id=$chat_id'>$firstname</a>.</b>

🔗Please send me a <a href='https://youtube.com'>YouTube</a> link.

🔴<b>Examples:</b>
1️⃣ https://youtu.be/IF0FVKSWz-I
2️⃣ https://www.youtube.com/watch?v=IF0FVKSWz-I
$sign";
    $bot->sendMessage($chat_id, $message, "HTML", true);
	#####################################################################################
}elseif(strpos($text, "youtube.com/watch?v=") !== false or strpos($text, "youtu.be/") !== false){
	$message = "♻️Please wait ...";
	$msgID = $bot->sendMessage($chat_id, $message, "HTML", true, null, null)->result->message_id;
	$r = YouTube($text);
	if($r['status'] != "successfully"){
		$message = "☹️<b>Ooops.</b>\n⚠️<b>Error Message:</b> <code>".ucfirst($r['message'])."</code>";
		$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, null);
		$bot->deleteMessage($chat_id, $msgID);
		exit;
	}
	$r = $r['result'];
	$caption = "💥<b>Title:</b> <code>".$r['title']."</code>\n
🗣<b>Publisher:</b> <a href='".$r['youtube_channel']['url']."'>".$r['youtube_channel']['title']."</a>
⏰<b>Time:</b> <code>".$r['duration']['minute']."</code>
📅<b>Published At:</b> <code>".$r['published_at']['time']." - ".$r['published_at']['date']."</code>

👁<b>View Count:</b> <code>".number_format($r['statistics']['view_count'])."</code>
👍<b>Like Count:</b> <code>".number_format($r['statistics']['like_count'])."</code>
❤️<b>Favorite Count:</b> <code>".number_format($r['statistics']['favorite_count'])."</code>
💬<b>Comment Count:</b> <code>".number_format($r['statistics']['comment_count'])."</code>

📝<b>Description:</b> <code>".$r['description']."</code>
$sign";
	if(strlen($caption) >= 1024){
		$caption = str_replace($r['description'], "The caption text is long.", $caption);
		$send = "yes";
	}
	$button = json_encode(['inline_keyboard' => [
	[['text' => "📥Download", 'callback_data' => "dl_".$r['videoId']], ['text' => "😎Author", 'url' => $r['youtube_channel']['url']], ['text' => "🎧MP3", 'callback_data' => "dlmp3_".$r['videoId']]],
	[['text' => "👁Watch on YouTube", 'url' => "https://www.youtube.com/watch?v=".$r['videoId']]],
	]]);
	$s = $bot->sendPhoto($chat_id, $r['image']['high']['url'], $caption, "HTML", null, $message_id, $button);
	if($send == "yes"){
		$message = "📝<b>Caption:</b> <code>".$r['description']."</code>\n$sign";
		$button = json_encode(['inline_keyboard' => [
    	[['text' => "👁Watch on YouTube", 'url' => "https://www.youtube.com/watch?v=".$r['videoId']]],
    	]]);
		$bot->sendMessage($chat_id, $message, "HTML", true, $s->result->message_id, $button);
	}
	$bot->deleteMessage($chat_id, $msgID);
	#####################################################################################
}elseif(strpos($data, "dl_") !== false){
	$bot->AnswerCallBack($callback_id, "♻️Please wait ...", false);
	$videoId = str_replace("dl_", null, $data);
	$r = YouTube($videoId);
	if($r['status'] != "successfully"){
		$message = "☹️<b>Ooops.</b>\n⚠️<b>Error Message:</b> <code>".ucfirst($r['message'])."</code>";
		$bot->sendMessage($chatID, $message, "HTML", true, $messageID, null);
		exit;
	}
	$r = $r['result'];
	if($r['downloads']['videos'] == null){
		$message = "☹️<b>Ooops.</b>\n⚠️<b>Error Message:</b> <code>Can't dump video download links.</code>";
		$bot->sendMessage($chatID, $message, "HTML", true, $messageID, null);
		exit;
	}
	foreach($r['downloads']['videos']['mp4'] as $formats){
		if($formats['extension'] == "mp4" && $formats['quality'] != null && $formats['file_size'] != null){
			$n++;
			$list .= "<b>".$n.": <a href='".$formats['url']."'>Download | ".$formats['quality']."p - ".SizeConvert($formats['file_size'])." - ".$formats['extension']."</a></b>\n";
		}
	}
	$message = "📥<b>Download Links:</b>\n\n$list".$sign;
	$button = json_encode(['inline_keyboard' => [
	[['text' => "👁Watch on YouTube", 'url' => "https://www.youtube.com/watch?v=".$r['videoId']]],
	]]);
	$bot->sendMessage($chatID, $message, "HTML", true, $messageID, $button);
	#####################################################################################
}elseif(strpos($data, "dlmp3_") !== false){
	$bot->AnswerCallBack($callback_id, "♻️Please wait ...", false);
	$videoId = str_replace("dlmp3_", null, $data);
	$r = YouTube($videoId);
	if($r['status'] != "successfully"){
		$message = "☹️<b>Ooops.</b>\n⚠️<b>Error Message:</b> <code>".ucfirst($r['message'])."</code>";
		$bot->sendMessage($chatID, $message, "HTML", true, $messageID, null);
		exit;
	}
	$r = $r['result'];
	if($r['downloads']['audios'] == null){
		$message = "☹️<b>Ooops.</b>\n⚠️<b>Error Message:</b> <code>Can't Convert video to MP3 format.</code>";
		$bot->sendMessage($chatID, $message, "HTML", true, $messageID, null);
		exit;
	}
	$message = "✅Video Converted to MP3 File Successfully.\n\n📥<b>Download Link:</b>\n🔗 ".$r['downloads']['audios']."\n$sign";
	$button = json_encode(['inline_keyboard' => [
	[['text' => "👁Watch on YouTube", 'url' => "https://www.youtube.com/watch?v=".$r['videoId']]],
	]]);
	$bot->sendMessage($chatID, $message, "HTML", true, $messageID, $button);
	#####################################################################################
}else{
    $bot->sendMessage($chat_id, "❌*Command not found.*\n$sign", "MarkDown");
	#####################################################################################
}
unlink("error_log");
?>
