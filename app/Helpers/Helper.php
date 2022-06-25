<?php
namespace App\Helpers;
use Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Input;

class Helper
{
    public static function customCrypt($vWord){
    	/**********************************************************************************************************************************************************
		#IMP_NOTE_:_if_you_need_to_change_below_custom_key_in_future_then_make_sure_its_length_should_be_32_characters_(now_its_32),_otherwise_it_will_stop_working.
    	***********************************************************************************************************************************************************/
	    $customKey = "GurdevhsSecretKeyGoodToGoForSucc"; 
	    $newEncrypter = new \Illuminate\Encryption\Encrypter( $customKey, Config::get( 'app.cipher' ) );
	    return $newEncrypter->encrypt( $vWord );
	}

	public static function customDecrypt($vWord){
		/**********************************************************************************************************************************************************
		#IMP_NOTE_:_if_you_need_to_change_below_custom_key_in_future_then_make_sure_its_length_should_be_32_characters_(now_its_32),_otherwise_it_will_stop_working.
		#This_is_the_same_key_used_in_above_function_to_encrypt
    	***********************************************************************************************************************************************************/
	    $customKey = "GurdevhsSecretKeyGoodToGoForSucc";
	    $newEncrypter = new \Illuminate\Encryption\Encrypter( $customKey, Config::get( 'app.cipher' ) );
	    return $newEncrypter->decrypt( $vWord );
	}

}