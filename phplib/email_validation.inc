<?
/*
 * email_validation.php
 *
 * @(#) $Header$
 *
 */

class email_validation_class
{
 var $username_regular_expression="^([a-zA-Z0-9_@-]{4,})\$";
 var $password_regular_expression="^([[:print:]]{4,})\$";
 var $name_regular_expression="^([a-zA-ZÄÖÜ][^0-9\\/\\\\\'\\`\\´\(\)]+)\$";
 var $telefon_regular_expression="^([0-9 \(\)\\/+_-]*)\$";
 var $email_regular_expression="^([a-z0-9_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,4}\$";
 var $timeout=0;
 var $localhost="";
 var $localuser="";
 var $debug=0;

 Function OutputDebug($message)
 {
  echo $message,"\n";
 }

 Function GetLine($connection)
 {
  for($line="";;)
  {
   if(feof($connection))
    return(0);
   $line.=fgets($connection,100);
   $length=strlen($line);
   if($length>=2
   && substr($line,$length-2,2)=="\r\n")
   {
    $line=substr($line,0,$length-2);
    if($this->debug)
     $this->OutputDebug("< $line");
    return($line);
   }
  }
 }

 Function PutLine($connection,$line)
 {
  if($this->debug)
   $this->OutputDebug("> $line");
  return(fputs($connection,"$line\r\n"));
 }

 Function ValidateUsername($username)
 {
  return(ereg($this->username_regular_expression,$username)!=0);
 }

 Function ValidatePassword($password)
 {
  return(eregi($this->password_regular_expression,$password)!=0);
 }

 Function ValidateName($name)
 {
  return(ereg($this->name_regular_expression,$name)!=0);
 }

 Function ValidateTelefon($telefon)
 {
  return(ereg($this->telefon_regular_expression,$telefon)!=0);
 }

 Function ValidateEmailAddress($email)
 {
  return(eregi($this->email_regular_expression,$email)!=0);
 }

 Function ValidateEmailHost($email,$hosts=0)
 {
  if(!$this->ValidateEmailAddress($email))
   return(0);
  $user=strtok($email,"@");
  $domain=strtok("");
  if(GetMXRR($domain,&$hosts,&$weights))
  {
   $mxhosts=array();
   for($host=0;$host<count($hosts);$host++)
    $mxhosts[$weights[$host]]=$hosts[$host];
   KSort($mxhosts);
   for(Reset($mxhosts),$host=0;$host<count($mxhosts);Next($mxhosts),$host++)
    $hosts[$host]=$mxhosts[Key($mxhosts)];
  }
  if (!count($hosts))
  {
   $hosts=array();
   if(strcmp(@gethostbyname($domain),$domain)!=0)
    $hosts[]=$domain;
  }
  return(count($hosts)!=0);
 }

 Function VerifyResultLines($connection,$code)
 {
  while(($line=$this->GetLine($connection)))
  {
   if(!strcmp(strtok($line," "),$code))
    return(1);
   if(strcmp(strtok($line,"-"),$code))
    return(0);
  }
  return(-1);
 }

 Function ValidateEmailBox($email)
 {
  if(!$this->ValidateEmailHost($email,&$hosts))
   return(0);
  if(!strcmp($localhost=$this->localhost,"")
  && !strcmp($localhost=getenv("SERVER_NAME"),"")
  && !strcmp($localhost=getenv("HOST"),""))
     $localhost="localhost";
  if(!strcmp($localuser=$this->localuser,"")
  && !strcmp($localuser=getenv("USERNAME"),"")
  && !strcmp($localuser=getenv("USER"),""))
     $localuser="root";
  for($host=0;$host<count($hosts);$host++)
  {
   if($this->debug)
    $this->OutputDebug("Connecting to host \"".$hosts[$host]."\"...");
   if(($connection=($this->timeout ? fsockopen($hosts[$host],25,&$errno,&$error,$this->timeout) : fsockopen($hosts[$host],25))))
   {
    if($this->debug)
     $this->OutputDebug("Connected.");
    if($this->VerifyResultLines($connection,"220")>0
    && $this->PutLine($connection,"HELO $localhost")
    && $this->VerifyResultLines($connection,"250")>0
    && $this->PutLine($connection,"MAIL FROM: <$localuser@$localhost>")
    && $this->VerifyResultLines($connection,"250")>0
    && $this->PutLine($connection,"RCPT TO: <$email>")
    && ($result=$this->VerifyResultLines($connection,"250"))>=0)
    {
     if($this->debug)
      $this->OutputDebug("This host states that the address is ".($result ? "" : "not ")."valid.");
     fclose($connection);
     if($this->debug)
      $this->OutputDebug("Disconnected.");
     return($result);
    }
    if($this->debug)
     $this->OutputDebug("Unable to validate the address with this host.");
    fclose($connection);
    if($this->debug)
     $this->OutputDebug("Disconnected.");
   }
   else
   {
    if($this->debug)
     $this->OutputDebug("Failed.");
   }
   
  }
  return(-1);
 }
};

?>
