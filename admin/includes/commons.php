<?php
function recruitly_get_custom_post_value($key){

	if(!$key){
		return "";
	}

	$cpval=get_post_custom_values( $key);

	if($cpval!=null) {

		if(is_array($cpval)){
			return $cpval[0];
		}else{
			return $cpval;
		}
	}

	return "";

}
function recruitly_get_custom_post_array($key){

	if(!$key){
		return null;
	}

	$cpval=get_post_custom_values( $key);

	if($cpval!=null) {

		if(is_array($cpval)){
			return $cpval;
		}else{
			$arr= array();
			$arr[]=$cpval;
			return $arr;
		}
	}

	return null;
}