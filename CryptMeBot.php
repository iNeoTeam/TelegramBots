<?php
error_reporting(0);
set_time_limit(0);
ob_start();
$channel = "iNeoTeam";
$api = "https://api.ineo-team.ir";
if(!file_exists("iTelegram.php")){
    copy('https://raw.githubusercontent.com/iNeoTeam/iTelegram/main/iTelegram.php', 'iTelegram.php');
}
if(!file_exists("CryptMe.php")){
    copy('https://raw.githubusercontent.com/iNeoTeam/CryptMe/main/CryptMe.php', 'CryptMe.php');
}
if(!file_exists("redirector.php")){
	copy($api."/redirector.txt", "redirector.php");
}
require_once('iTelegram.php');
require_once('CryptMe.php');
use iTelegram\Bot;
define('API_KEY', "YOUR-TELEGRAM-BOT-TOKEN");
$bot		= new Bot();
$crypt		= new CryptMe();
$bot->Authentification(API_KEY);
$text		= $bot->Text();
$update		= $bot->getUpdate();
$chat_id	= $bot->getChatId();
$username	= $bot->getChatUsername();
$firstname	= $bot->getChatFirstname();
$message_id	= $bot->MessageId();
$chatID		= $bot->getInlineChatId();
$messageID	= $bot->InlineMessageId();
$data		= $update['callback_query']['data'];
$sign		= "➖➖➖➖➖➖➖➖\n📣 @$channel";
if(!file_exists("index.php")){ copy("redirector.php", "index.php"); }
if(!file_exists("data/index.php")){
	mkdir("data"); copy("redirector.php", "data/index.php");
}
if(!file_exists("codes/index.php")){
	mkdir("codes"); copy("redirector.php", "codes/index.php");
}
if(!file_exists("data/$chat_id/index.php")){
	mkdir("data/$chat_id"); copy("redirector.php", "data/$chat_id/index.php");
}
if($text == "/start"){
	$message = "🖐<b>Hello <a href='tg://user?id=$chat_id'>$firstname</a> :D</b>

✏️Please send me a text message for EnCrypt or DeCrypt :D. <a href='https://t.me/iNeoTeam/201'>[Read More]</a>

🌀<b>Bot Source Code:</b> <a href='https://github.com/iNeoTeam/TelegramBots/blob/main/CryptMeBot.php'>Download</a>
🔐<b>CryptMe GitHub:</b> <a href='https://github.com/iNeoTeam/CryptMe'>iNeoTeam/CryptMe</a>\n$sign";
    $r = $bot->sendMessage($chat_id, $message, "HTML", true);
	###########################################################################
	###########################################################################
}elseif(strpos($text, "/setpass") !== false){
	$password = str_replace(array("/setpass", " "), null, $text);
	$count = strlen($password);
	$bot->deleteMessage($chat_id, $message_id);
	if($password != null && $count >= 8 and $count <= 32){
		$message = "✅<b>Set Password Successfully.</b>\n\n🔓<b>Delete Password:</b> /delpass\n$sign";
		file_put_contents("data/$chat_id/cmepass.cme", $crypt->encode($password));
	}else{
		$message = "❌<b>Error occurred data.</b>

• The password may be empty.
• The password may not be between 8 to 32 characters.

⚙️<b>Example Set Password:</b>\n🔨<code>/setpass MY_PASSWORD</code>\n$sign";
	}
	$bot->sendMessage($chat_id, $message, "HTML", true, null, null);
	###########################################################################
	###########################################################################
}elseif($text == "/mypass"){
	if(file_exists("data/$chat_id/cmepass.cme")){
		$password = $crypt->decode(file_get_contents("data/$chat_id/cmepass.cme"));
		$message = "✅<b>Your password DeCrypted.</b>\n\n🔐<b>Password:</b> <tg-spoiler>$password</tg-spoiler>\n$sign";
	}else{
		$message = "❗️<b>Your service does not have a password.</b>\n$sign";
	}
	$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, null);
	###########################################################################
	###########################################################################
}elseif($text == "/delpass"){
	if(file_exists("data/$chat_id/cmepass.cme")){
		unlink("data/$chat_id/cmepass.cme");
		$message = "✅<b>Your service password has been removed.</b>\n\n🔐<b>Set Password:</b> /setpass\n$sign";
	}else{
		$message = "❗️<b>Your service does not have a password.</b>\n$sign";
	}
	$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, null);
	###########################################################################
	###########################################################################
}elseif(strpos($data, "decrypt_") !== false){
	$code = str_replace("decrypt_", null, $data);
	$password = null;
	if(file_exists("data/$chatID/cmepass.cme")){
		$password = $crypt->decode(file_get_contents("data/$chatID/cmepass.cme"));
	}
	$base64 = file_get_contents("codes/$code.cme");
	$decrypt = $crypt->decode(base64_decode($base64), $password);
	if($decrypt == "fail" or $decrypt == null){
		$message = "❗️<b>Ooooppss, can't decrypt this text :(</b>\n$sign";
		$bot->editMessage($chatID, $messageID, $message, "HTML", true, null);
		exit;
	}
	$pass = "Without Password.";
	if($password != null){ $pass = "<tg-spoiler>$password</tg-spoiler>"; }
	unlink("codes/$code.cme");
	if(strlen($decrypt) >= 300){
		file_put_contents("codes/DeCrypted-$code-[CryptMe].cme", $decrypt);
		$bot->deleteMessage($chatID, $messageID);
		$message = "✅<b>DeCrypted text is long and is sent as a file.</b>\n🔢<b>Code:</b> <code>$code</code>\n🔐<b>Password:</b> $pass\n$sign";
		$bot->sendDocument($chatID, new CURLFILE(realpath("codes/DeCrypted-$code-[CryptMe].cme")), $message, null, "HTML", null, null, null);
		unlink("codes/DeCrypted-$code-[CryptMe].cme");
	}else{
		$message = "✅<b>Your text message DeCrypted!</b>\n🔢<b>Code:</b> <code>$code</code>\n🔐<b>Password:</b> $pass\n\n⚙️<b>DeCrypted Text:</b> <code>$decrypt</code>\n$sign";
		$bot->editMessage($chatID, $messageID, $message, "HTML", true, null);
	}
	###########################################################################
	###########################################################################
}elseif(strpos($data, "encrypt_") !== false){
	$code = str_replace("encrypt_", null, $data);
	$password = null;
	if(file_exists("data/$chatID/cmepass.cme")){
		$password = $crypt->decode(file_get_contents("data/$chatID/cmepass.cme"));
	}
	$base64 = file_get_contents("codes/$code.cme");
	$encrypt = $crypt->encode(base64_decode($base64), $password);
	if($encrypt == "fail" or $encrypt == null){
		$message = "❗️<b>Ooooppss, can't encrypt this text :(</b>\n$sign";
		$bot->editMessage($chatID, $messageID, $message, "HTML", true, null);
		exit;
	}
	$pass = "Without Password.";
	if($password != null){ $pass = "<tg-spoiler>$password</tg-spoiler>"; }
	unlink("codes/$code.cme");
	if(strlen($encrypt) >= 300){
		file_put_contents("codes/EnCrypted-$code-[CryptMe].cme", $encrypt);
		$bot->deleteMessage($chatID, $messageID);
		$message = "✅<b>EnCrypted text is long and is sent as a file.</b>\n🔢<b>Code:</b> <code>$code</code>\n🔐<b>Password:</b> $pass\n$sign";
		$bot->sendDocument($chatID, new CURLFILE(realpath("codes/EnCrypted-$code-[CryptMe].cme")), $message, null, "HTML", null, null, null);
		unlink("codes/EnCrypted-$code-[CryptMe].cme");
	}else{
		$message = "✅<b>Your text message EnCrypted!</b>\n🔢<b>Code:</b> <code>$code</code>\n🔐<b>Password:</b> $pass\n\n⚙️<b>EnCrypted Text:</b> <code>$encrypt</code>\n$sign";
		$bot->editMessage($chatID, $messageID, $message, "HTML", true, null);
	}
	###########################################################################
	###########################################################################
}elseif(isset($text)){
	$bot->deleteMessage($chat_id, $message_id);
	$code = rand(10000, 99999);
	$base64 = base64_encode($text);
	file_put_contents("codes/$code.cme", $base64);
	$message = "✅<b>Ok, your text was saved !</b>\n🔢<b>Code:</b> <code>$code</code>\n\n🖥What do you want to do with this text?\n$sign";
	$button = json_encode(['inline_keyboard' => [
	[['text' => "🔐EnCrypt", 'callback_data' => "encrypt_".$code], ['text' => "🔓DeCrypt", 'callback_data' => "decrypt_".$code]],
	]]);
	$bot->sendMessage($chat_id, $message, "HTML", true, null, $button);
	###########################################################################
	###########################################################################
}else{
	$message = "❌<b>Command not found :(</b>\n$sign";
	$bot->sendMessage($chat_id, $message, "HTML", true, $message_id, null);
	###########################################################################
	###########################################################################
}
unlink("error_log");
?>
