<?php
class session
{
	var $db=NULL;
	var $SESS_LIFE=NULL;
	var $is_have=NULL;
	
	function session()
	{
		global $db;
		
		$this->db=$db;
		
		ini_set("session.save_handler", "user");
		//session_module_name();
		//得到session的最大有效期。
		$this->SESS_LIFE=get_cfg_var("session.gc_maxlifetime");
		//register_shutdown_function('session_write_close');
	}
	
	function open()
	{
		return true;
	}
	
	function close()
	{
		return true;
	}
	
	function read($key)
	{
		$sql="select value,sesskey,expiry from ".SESSION." where sesskey = '$key'";
		$this->db->query($sql);
		$re=$this->db->fetchRow();
		$this->is_have=$re['sesskey'];
		if(!empty($re['value'])&&time()<$re['expiry'])
			return $re['value'];
		else
			return false;
	}
	
	function write($key,$value)
	{
		$expiry=time()+$this->SESS_LIFE;
		if(!empty($this->is_have))
			$sql="update ".SESSION." set expiry='$expiry', value='$value' where sesskey='$key'";
		else
			$sql="insert into ".SESSION." values('$key',$expiry,'$value')";
		$re=$this->db->query($sql);
		if($re)
			return true;
		else
			return false;
	}
	
	function destroy($key)
	{
		$qry="delete from ".SESSION." where sesskey = '$key'";
		$qid=$this->db->query($qry);
		return $qid;
	}
	
	function gc($maxlifetime)
	{
		$qry="delete from ".SESSION." where expiry < ".time();
		$qid=$this->db->query($qry);
		return true;
	}
	
	function start()
	{
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);
	   session_start();
	}
}
$sess=new session();
$sess->start();
?>