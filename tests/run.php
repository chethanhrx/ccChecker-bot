<?php
declare(strict_types=1);
require __DIR__.'/../functions.php';
$failed=0;$passed=0;
function check(bool $ok,string $name):void{global $failed,$passed;if($ok){$passed++;echo ".";}else{$failed++;echo "F\nFAIL: {$name}\n";}}
$commands=bot_commands();
check(isset($commands['start'],$commands['cmd'],$commands['register'],$commands['chk']), 'core commands auto-discovered');
check(isset($commands['hello']), 'hello command auto-discovered');
check(bot_demo_result('auth')['status']==='SIMULATED_OK','auth demo remains simulated');
check(bot_demo_result('charge')['status']==='SIMULATED_OK','charge demo remains simulated');
check(bot_demo_result('unknown')['status']==='SIMULATED_UNKNOWN','unknown demo is safe');
$dir=sys_get_temp_dir().'/ccbot-test-'.bin2hex(random_bytes(5));mkdir($dir,0775,true);
$original=bot_profiles_path(); // storage is redirected for this isolated test below.
$GLOBALS['bot_test_storage_path']=$dir.'/users.json';
check(bot_user_find(123456789)===null,'missing user returns null');
$p=bot_user_register(['id'=>123456789,'first_name'=>'Test','username'=>'tester']);
check(($p['telegram_id']??'')==='123456789','register saves Telegram ID');
$r=bot_user_redeem(123456789,'DEMO-STARTER');
check(($r['status']??'')==='redeemed'&&($r['balance']??0)===10,'demo code adds credits');
$r2=bot_user_redeem(123456789,'DEMO-STARTER');
check(($r2['status']??'')==='already_redeemed'&&($r2['balance']??0)===10,'demo code can only be redeemed once');
check(bot_user_redeem(123456789,'NOPE')['status']==='invalid_code','invalid code rejected');
check(bot_user_delete(123456789)&&bot_user_find(123456789)===null,'delete removes local profile');
@unlink($dir.'/users.json');@unlink($dir.'/users.json.tmp');@rmdir($dir);
echo "\n{$passed} checks, {$failed} failures\n";exit($failed===0?0:1);
