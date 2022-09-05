<?php
error_reporting(0);
set_time_limit(0);
ob_start();
if(!file_exists("iTelegram.php")){
    copy('https://raw.githubusercontent.com/iNeoTeam/iTelegram/main/iTelegram.phar', 'iTelegram.php');
}
require_once('iTelegram.php');
use iTelegram\Bot;
$channel		= "USERNAME";
$admin			= "1234567890";
$api			= "https://api.ineo-team.ir"; # don't change it.
define('API_KEY', "TELEGRAM_BOT_TOKEN");
function safe($input){
	$array = ['$', ';', '"', "'", '<', '>'];
	return str_replace($array, null, $input);
}
function timedate(){
	global $api;
	$time = json_decode(file_get_contents($api."/timezone.php?action=time&zone=fa"));
	$date = json_decode(file_get_contents($api."/timezone.php?action=date&zone=fa"));
	return ['time' => $time->result->time, 'date' => $date->result->date];
}
function AddUser($chat_id){
	if(!is_dir("data")){ mkdir("data"); }
	if(!is_dir("data/".$chat_id)){ mkdir("data/".$chat_id); }
	copy("redirector.php", "data/index.php");
	copy("redirector.php", "data/".$chat_id."/index.php");
	$users = file_get_contents("data/userslist.txt");
	if(!in_array($chat_id, explode("\n", $users))){
		$users .= $chat_id."\n";
		file_put_contents("data/userslist.txt", $users);
	}
}
$bot		= new Bot();
$bot->Authentification(API_KEY);
$update		= $bot->getUpdate();
$text		= safe($bot->Text());
$chat_id	= $bot->UserId();
$username	= $bot->Username();
$firstname	= safe($bot->Firstname());
$message_id	= $bot->MessageId();
$chatID		= $bot->InlineUserId();
$messageID	= $bot->InlineMessageId();
$data		= $update['callback_query']['data'];
$callbackId = $update['callback_query']['id'];
$getStep	= file_get_contents("data/".$chat_id."/step.txt");
$cancelBtn	= json_encode(['inline_keyboard' => [[['text' => "❌لغو عملیات", 'callback_data' => "cancel"]]]]);
$backBtn	= json_encode(['inline_keyboard' => [[['text' => "🔙برگشت به پنل مدیریت", 'callback_data' => "adminlogin"]]]]);
if(isset($chat_id) && $bot->getChatType() != "private"){ exit; }
AddUser($chat_id);
$commands	= json_encode([
['command' => base64_decode("c3RhcnQ="), 'description' => base64_decode("2LTYsdmI2Lkg2Ygg2LHYp9mHINin2YbYr9in2LLbjCDZhdis2K/YryDYsdio2KfYqg==")],
['command' => base64_decode("Y3JlYXRvcg=="), 'description' => base64_decode("2LfYsdin2K3bjCDZiCDYqtmI2LPYudmHINix2KjYp9iq")]
]);
$bot->TelegramAPI("setMyCommands", ['commands' => $commands]);
$sign = "➖➖➖➖➖➖➖➖\n📣 @$channel";
if(isset($chat_id) && in_array($chat_id, explode("\n", file_get_contents("data/blockeduserslist.txt")))){
	$message = "⛔️حساب کاربری شما بلاک شده است و امکان ارسال پیام توسط شما وجود ندارد.\n$sign";
	$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, null); exit;
}
if($text == "/start" && $chat_id != $admin){
	file_put_contents("data/$chat_id/name.txt", $firstname);
	file_put_contents("data/$chat_id/step.txt", "none");
	$message = "🖐<b>سلام <a href='tg://user?id=".$chat_id."'>".$firstname."</a> عزیز.</b>

📝لطفا پیام خود را بنویسید یا مدیا مورد نظر خود را ارسال کنید.\n$sign";
	$button = json_encode(['inline_keyboard' => [
	[['text' => "🗂دانلود سورس ربات پیام رسان آی نئو", 'url' => "https://t.me/iNeoTeam/208"]],
	]]);
    $r = $bot->sendMessage($chat_id, $message, "HTML", true, $message_id, $button);
	###################################################################################################
}elseif($text == "/start" && $chat_id == $admin){
	file_put_contents("data/".$chat_id."/step.txt", "none");
	$message = "🖐<b>سلام <a href='tg://user?id=".$admin."'>مدیر</a> گرامی.</b>

🖥جهت ورود به پنل مدیریت، بر روی دکمه زیر کلیک کنید.\n$sign";
	$button = json_encode(['inline_keyboard' => [
	[['text' => "🖥ورود به پنل مدیریت ربات", 'callback_data' => "adminlogin"]],
	]]);
	$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, $button);
	###################################################################################################
}elseif(strtolower($text) == "/creator"){
	file_put_contents("data/$chat_id/step.txt", "none");
	$message = "✅سورس این ربات، توسط <a href='https://t.me/iNeoTeam'>گروه ربات سازی و خدمات مجازی آی نئو</a> طراحی شده است.\n\n📥جهت دانلود این سورس این ربات، بر روی لینک زیر کلیک کنید.
🔗 https://T.me/iNeoTeam/208\n$sign";
	$button = json_encode(['inline_keyboard' => [
	[['text' => "🗂دانلود سورس ربات پیام رسان آی نئو", 'url' => "https://t.me/iNeoTeam/208"]],
	]]);
	$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, $button);
	###################################################################################################
}elseif($data == "cancel"){
	file_put_contents("data/".$chatID."/step.txt", "none");
	$message = "❌عملیات با موفقیت لغو شده است.";
	$bot->AnswerCallBack($callbackId, $message, true);
	$bot->deleteMessage($chatID, $messageID);
	###################################################################################################
}elseif($data == "cl"){
	file_put_contents("data/".$chatID."/step.txt", "none");
	$message = "✅پنل مدیریت با موفقیت بسته شده است.";
	$bot->AnswerCallBack($callbackId, $message, true);
	$bot->deleteMessage($chatID, $messageID);
	###################################################################################################
}elseif(strpos($data, "r2_usr:") !== false && strpos($data, "&msgId:") !== false && $chatID == $admin){
	file_put_contents("data/".$chatID."/step.txt", "sendAnswerTo_data:".base64_encode($data));
	preg_match('#r2_usr:(.*?)&msgId:(.*)#su', $data, $output);
	$name = file_get_contents("data/".$output[1]."/name.txt");
	$message = "📝پیام خود را جهت ارسال به <a href='tg://user?id=".$output[1]."'>$name</a> با شناسه کاربری <code>".$output[1]."</code> ارسال کنید.

✅<b>محتوای مجاز به ارسال:</b>\n🗂متن، عکس، فیلم، ویس، فایل و آهنگ\n$sign";
	$bot->sendMessage($chatID, $message, "HTML", true, $messageID, $cancelBtn); $data = null;
	###################################################################################################
}elseif($chat_id == $admin && strpos($getStep, "sendAnswerTo_data:") !== false){
	file_put_contents("data/$chat_id/step.txt", "none");
	$getStep = base64_decode(str_replace('sendAnswerTo_data:', null, $getStep));
	preg_match('#r2_usr:(.*?)&msgId:(.*)#su', $getStep, $output);
	$name = file_get_contents("data/".$output[1]."/name.txt");
	$type = $bot->InputMessageType();
	$text = $update['message']['text'] ?? $update['message']['caption'];
	$caption = safe($text);
	$timedate = timedate();
	$button = json_encode(['inline_keyboard' => [
	[['text' => "⏰".$timedate['time'], 'callback_data' => "nothing"], ['text' => "📆".$timedate['date'], 'callback_data' => "nothing"]],
	]]);
	if($type == "text"){
		$bot->sendMessage($output[1], $caption, "HTML", true, $output[2], $button);
	}elseif($type == "document"){
		$bot->sendDocument($output[1], $update['message']['document']['file_id'], $caption, null, "HTML", null, $output[2], $button);
	}elseif($type == "audio"){
		$bot->sendAudio($output[1], $update['message']['audio']['file_id'], $caption, null, null, null, null, "HTML", null, $output[2], $button);
	}elseif($type == "voice"){
		$bot->sendVoice($output[1], $update['message']['voice']['file_id'], $caption, null, "HTML", null, $output[2], $button);
	}elseif($type == "video"){
		$bot->sendVideo($output[1], $update['message']['video']['file_id'], $caption, "HTML", null, $output[2], $button);
	}elseif($type == "photo"){
		$count = count($update['message']['photo']) - 1;
		$bot->sendPhoto($output[1], $update['message']['photo'][$count]['file_id'], $caption, "HTML", null, $output[2], $button);
	}elseif($type == "sticker"){
		$bot->sendSticker($output[1], $update['message']['sticker']['file_id'], null, $output[2], $button);
	}
	$message = "✅پاسخ شما با موفقیت برای <a href='tg://user?id=".$output[1]."'>$name</a> با شناسه کاربری <code>".$output[1]."</code> ارسال شد.\n$sign";
	$button = json_encode(['inline_keyboard' => [
	[['text' => "⏰".$timedate['time'], 'callback_data' => "nothing"], ['text' => "📆".$timedate['date'], 'callback_data' => "nothing"]],
	[['text' => "✅آنبلاک کردن", 'callback_data' => "unblockthisuser_".$output[1]], ['text' => "❌بلاک کردن", 'callback_data' => "blockt_hisuser_".$output[1]]],
	[['text' => "📝ارسال مجدد پاسخ", 'callback_data' => $getStep]],
	]]);
	$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, $button);
	###################################################################################################
}elseif($data == "adminlogin" && $chatID == $admin){
	file_put_contents("data/".$chatID."/step.txt", "none");
	$message = "🖐با سلام مدیر گرامی

❤️به پنل مدیریت ربات خوش آمدید.\n$sign";
	$button = json_encode(['inline_keyboard' => [
	[['text' => "✅آنبلاک کردن", 'callback_data' => "ubu"], ['text' => "❌بلاک کردن", 'callback_data' => "bu"]],
	[['text' => "📝ارسال همگانی", 'callback_data' => "s2a"], ['text' => "🔄فوروارد همگانی", 'callback_data' => "f2a"]],
	[['text' => "✖️بستن پنل", 'callback_data' => "cl"], ['text' => "📊فعالیت ربات", 'callback_data' => "ac"]],
	]]);
	$bot->editMessage($chatID, $messageID, $message, "HTML", true, $button);
	###################################################################################################
}elseif(in_array($data, ['ubu', 'bu']) && $chatID == $admin){
	if($data == "ubu"){
		$method = "unblock";
		$methodFA = "آنبلاک";
	}else{
		$method = "block";
		$methodFA = "بلاک";
	}
	file_put_contents("data/$chatID/step.txt", "getId4_$method");
	$message = "🆔شناسه کاربری شخص مورد نظر را جهت $methodFA کردن ارسال کنید.\n$sign";
	$bot->editMessage($chatID, $messageID, $message, "HTML", true, $cancelBtn);
	###################################################################################################
}elseif(isset($text) && strpos($getStep, "getId4_") !== false){
	file_put_contents("data/$chat_id/step.txt", "none");
	$method = str_replace("getId4_", null, $getStep);
	$id = safe($text);
	$users = file_get_contents("data/userslist.txt");
	$blocked = file_get_contents("data/blockeduserslist.txt");
	if(!in_array($id, explode("\n", $users))){
		$message = "❌شناسه کاربری مورد نظر در دیتابیس پیدا نشده است.\n$sign";
		$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, $backBtn);
		exit;
	}
	if($method == "block"){
		if(in_array($id, explode("\n", $blocked))){
			$message = "❌کاربر مورد نظر با شناسه کاربری <code>$id</code> از قبل در لیست بلاک شده ها بوده است.\n$sign";
		}else{
			$blocked .= $id."\n";
			file_put_contents("data/blockeduserslist.txt", $blocked);
			$message = "❌حساب شما توسط پشتیبانی بلاک شده است.\n$sign";
			$bot->sendMessage($id, $message, "HTML", true, null, null);
			$message = "✅کاربر مورد نظر با شناسه کاربری <code>$id</code> با موفقیت بلاک شده است.\n$sign";
		}
	}else{
		if(in_array($id, explode("\n", $blocked))){
			$blocked = str_replace($id."\n", null, $blocked);
			file_put_contents("data/blockeduserslist.txt", $blocked);
			$message = "✅حساب شما توسط پشتیبانی آنبلاک شده است.\n$sign";
			$bot->sendMessage($id, $message, "HTML", true, null, null);
			$message = "✅کاربر مورد نظر با شناسه کاربری <code>$id</code> با موفقیت آنبلاک شده است.\n$sign";
		}else{
			$message = "❌کاربر مورد نظر با شناسه کاربری <code>$id</code> در لیست بلاک شده ها وجود ندارد.\n$sign";
		}
	}
	$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, $backBtn);
	###################################################################################################
}elseif($data == "f2a" && $chatID == $admin){
	file_put_contents("data/$chatID/step.txt", "getForward");
	$message = "🔄پیام خود را جهت فوروارد همگانی فوروارد کنید.\n$sign";
	$bot->editMessage($chatID, $messageID, $message, "HTML", true, $cancelBtn);
	###################################################################################################
}elseif($getStep == "getForward" && $chat_id == $admin){
	file_put_contents("data/$chat_id/step.txt", "none");
	$message = "♻️لطفا کمی صبر کنید ...";
	$msgId = $bot->sendMessage($chat_id, $message, "HTML", true, $message_id, null)->result->message_id;
	$users = fopen("data/userslist.txt", 'r');
	while(!feof($users)){
		$user = fgets($users);
		$bot->forwardMessage($user, $chat_id, $message_id);
	}
	$bot->deleteMessage($chat_id, $msgId);
	$message = "✅پیام شما برای تمام کاربران فوروارد شد.\n$sign";
	$bot->sendMessage($chat_id, $message, "HTML", true, null, $backBtn);
	###################################################################################################
}elseif($data == "s2a" && $chatID == $admin){
	file_put_contents("data/".$chatID."/step.txt", "getMessage");
	$message = "📝لطفا پیام خود را جهت ارسال همگانی ارسال کنید.\n$sign";
	$bot->editMessage($chatID, $messageID, $message, "HTML", true, $cancelBtn);
	###################################################################################################
}elseif(isset($text) && $getStep == "getMessage" && $chat_id == $admin){
	file_put_contents("data/".$chat_id."/step.txt", "none");
	$text = safe($text);
	$message = "♻️لطفا کمی صبر کنید ...";
	$msgId = $bot->sendMessage($chat_id, $message, "HTML", true, $message_id, null)->result->message_id;
	$users = fopen("data/userslist.txt", 'r');
	$message = "📝<b>پیام همگانی از طرف پشتیبانی:</b>\n\n💬<b>متن پیام:</b> <code>$text</code>\n$sign";
	while(!feof($users)){
		$user = fgets($users);
		$bot->sendMessage($user, $message, "HTML", true, null, null);
	}
	$bot->deleteMessage($chat_id, $msgId);
	$message = "✅پیام شما برای تمام کاربران با موفقیت ارسال شد.\n$sign";
	$bot->sendMessage($chat_id, $message, "HTML", true, null, $backBtn);
	###################################################################################################
}elseif($data == "ac" && $chatID == $admin){
	file_put_contents("data/".$chatID."/step.txt", "none");
	$users = count(explode("\n", file_get_contents("data/userslist.txt"))) - 1;
	$blocked = count(explode("\n", file_get_contents("data/blockeduserslist.txt"))) - 1;
	$timedate = timedate();
	$message = "📊<b>فعالیت اخیر ربات:</b> <code>".$timedate['time']." - ".$timedate['date']."</code>

🌐<b>پینگ سرور:</b> <code>".sys_getloadavg()[2]."ms</code>
⚙️<b>ورژن PHP سرور:</b> <code>".phpversion()."</code>
🗂<b>ورژن بیس ربات:</b> <code>".$bot->version()."</code>
⚡️<b>رم مصرفی سرور:</b> <code>".number_format(memory_get_usage(true))." KB</code>
👥<b>تعداد کاربران:</b> <code>$users نفر</code>
⛔️<b>تعداد بلاک شده ها:</b> <code>$blocked نفر</code>
$sign";
	$bot->editMessage($chatID, $messageID, $message, "HTML", true, $backBtn);
	###################################################################################################
}elseif(strpos($data, "show_") !== false && $chatID == $admin){
	file_put_contents("data/$chatID/step.txt", "none");
	$id = str_replace("show_", null, $data);
	$name = file_get_contents("data/$id/name.txt");
	$message = "✏️پیام توسط <a href='tg://user?id=$id'>$name</a> با شناسه کاربری <code>$id</code> ارسال شده است.\n$sign";
	$bot->sendMessage($chatID, $message, "HTML", true, $messageID, null);
	###################################################################################################
}elseif(strpos($data, "unblockthisuser_") !== false && $chatID == $admin){
	file_put_contents("data/$chatID/step.txt", "none");
	$id = str_replace("unblockthisuser_", null, $data);
	$blocked = file_get_contents("data/blockeduserslist.txt");
	if(in_array($id, explode("\n", $blocked))){
		$blocked = str_replace($id."\n", null, $blocked);
		file_put_contents("data/blockeduserslist.txt", $blocked);
		$message = "✅حساب شما توسط پشتیبانی آنبلاک شده است.\n$sign";
		$bot->sendMessage($id, $message, "HTML", true, null, null);
		$message = "✅حساب کاربری مورد نظر با موفقیت آنبلاک شده است.";
	}else{
		$message = "❗️کاربر مورد نظر در لیست بلاک شده ها وجود ندارد.";
	}
	$bot->AnswerCallBack($callbackId, $message, true);
	###################################################################################################
}elseif(strpos($data, "blockt_hisuser_") !== false && $chatID == $admin){
	file_put_contents("data/$chatID/step.txt", "none");
	$id = str_replace("blockt_hisuser_", null, $data);
	$blocked = file_get_contents("data/blockeduserslist.txt");
	if(!in_array($id, explode("\n", $blocked))){
		$blocked .= $id."\n";
		file_put_contents("data/blockeduserslist.txt", $blocked);
		$message = "❌حساب شما توسط پشتیبانی بلاک شده است.\n$sign";
		$bot->sendMessage($id, $message, "HTML", true, null, null);
		$message = "✅حساب کاربری مورد نظر با موفقیت بلاک شده است.";
	}else{
		$message = "❗️کاربر مورد نظر از قبل بلاک بوده است.";
	}
	$bot->AnswerCallBack($callbackId, $message, true);
	###################################################################################################
}else{
	if($chat_id == $admin){
		$message = "❗️شما مدیر ربات هستید و نمیتوانید پیام پشتیبانی برای خودتان ارسال کنید.\n$sign";
		$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, $backBtn); exit;
	}
	file_put_contents("data/".$chat_id."/step.txt", "none");
	$timedate = timedate();
	$button = json_encode(['inline_keyboard' => [
	[['text' => "⏰".$timedate['time'], 'callback_data' => "nothing"], ['text' => "📆".$timedate['date'], 'callback_data' => "nothing"]],
	[['text' => "✅آنبلاک کردن", 'callback_data' => "unblockthisuser_".$chat_id], ['text' => "❌بلاک کردن", 'callback_data' => "blockt_hisuser_".$chat_id]],
	[['text' => "📝ارسال پاسخ", 'callback_data' => "r2_usr:".$chat_id."&msgId:".$message_id], ['text' => "👤کاربر", 'callback_data' => "show_".$chat_id]],
	]]);
	$button2 = json_encode(['inline_keyboard' => [
	[['text' => "⏰".$timedate['time'], 'callback_data' => "nothing"], ['text' => "📆".$timedate['date'], 'callback_data' => "nothing"]],
	]]);
	if($bot->InputMessageType() == "document"){
		$bot->sendDocument($admin, $update['message']['document']['file_id'], safe($update['message']['caption']), null, "HTML", null, null, $button);
	}elseif($bot->InputMessageType() == "audio"){
		$bot->sendAudio($admin, $update['message']['audio']['file_id'], safe($update['message']['caption']), null, null, null, null, "HTML", null, null, $button);
	}elseif($bot->InputMessageType() == "voice"){
		$bot->sendVoice($admin, $update['message']['voice']['file_id'], safe($update['message']['caption']), null, "HTML", null, null, $button);
	}elseif($bot->InputMessageType() == "video"){
		$bot->sendVideo($admin, $update['message']['video']['file_id'], safe($update['message']['caption']), "HTML", null, null, $button);
	}elseif($bot->InputMessageType() == "photo"){
		$count = count($update['message']['photo']) - 1;
		$photo = $update['message']['photo'][$count]['file_id'];
		$bot->sendPhoto($admin, $photo, safe($update['message']['caption']), "HTML", null, null, $button);
	}elseif($bot->InputMessageType() == "text"){
		$text = safe($text);
		$message = $text;
		$bot->sendMessage($admin, $message, "HTML", true, null, $button);
	}else{
		$message = "❌ورودی مورد نظر نامعتبر است.

✅<b>محتوای مجاز به ارسال:</b>\n🗂متن، عکس، فیلم، ویس، فایل و آهنگ\n$sign";
		$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, null);
		exit;
	}
	$bot->sendMessage($chat_id, "✅پیام شما با موفقیت برای پشتیبانی ارسال شد.

❤️لطفا تا زمان دریافت پاسخ، شکیبا باشید.\n$sign", "HTML", true, $message_id, $button2);
	###################################################################################################
}
unlink("error_log");
?>
