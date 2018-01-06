<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Rss_reader {

   // --------------------------------------------------------------------
   /**
   * Rss reader
   */
   
   var $Common_Content = array();
   var $Common_Style ="p";
   var $Common_Date_Font = "size='-1'";

   // -------------------------------------------------------------------- 
      
   function RSS_Tags($item, $type)
   {
      $y = array();
      $y["title"] = $item->getElementsByTagName("title")->item(0)->firstChild->data;
      $y["link"] = $item->getElementsByTagName("link")->item(0)->firstChild->data;
      $y["description"] = $item->getElementsByTagName("description")->item(0)->firstChild->data;

      $tnl = $item->getElementsByTagName("pubDate");
      if($tnl->length == 0) $tnl = $item->getElementsByTagName("lastBuildDate");
      
      if($tnl->length != 0) $tnl =$tnl->item(0)->firstChild->data;    
      else $tnl = false;
    
      $y["updated"] = $tnl;    
      $y["type"] = $type;
        
      array_push($this->Common_Content, $y);        
   }

   // -------------------------------------------------------------------- 
   
   function RSS_Channel($channel)
   {
      $items = $channel->getElementsByTagName("item");

      foreach($items as $item){
        $this->RSS_Tags($item, 1);
      }
    }
    
   // -------------------------------------------------------------------- 
   
   function RSS_Retrieve($url)
   {
      $doc  = new DOMDocument();
      $doc->load($url);

      $channels = $doc->getElementsByTagName("channel");
      $this->Common_Content = array();
    
      foreach($channels as $channel){
        $this->RSS_Channel($channel);
      }

      return ( count($this->Common_Content) > 0);    
   }

   // -------------------------------------------------------------------- 
   
   function Atom_Tags($item)
   {
      $y = array();
      $y["title"] = $item->getElementsByTagName("title")->item(0)->firstChild->data;
      $y["link"] = $item->getElementsByTagName("link")->item(0)->getAttribute("href");
    
      $summary = $item->getElementsByTagName("summary")->item(0)->firstChild->data;
      $content =  $item->getElementsByTagName("content")->item(0)->firstChild->data;
      $y["description"] = ($summary) ? $sumary : $content; 
    
      $y["updated"] = $item->getElementsByTagName("updated")->item(0)->firstChild->data; 
         
      array_push($this->Common_Content, $y);        
   }
   
   // -------------------------------------------------------------------- 
   
   function Atom_Feed($doc)
   {
      $entries = $doc->getElementsByTagName("entry");    

      if($entries->length == 0) return false;

      foreach($entries as $entry){
        $this->Atom_Tags($entry);   
      }
    
      return true;
   }

   // -------------------------------------------------------------------- 
   
   function Atom_Retrieve($url)
   {
      $doc  = new DOMDocument();
      $doc->load($url);

      $this->Common_Content = array();
    
      return $this->Atom_Feed($doc);
   }

   // -------------------------------------------------------------------- 
   
   function Retrieve($url, $size = 100, $key=0)
   {
      if($this->Atom_Retrieve($url) === false){
        if($this->RSS_Retrieve($url) === false)
          return 0;
      }
          
      if($size > 0){
        $size += 1; 
        $recents = array_slice($this->Common_Content, 0, $size);
      }    
      
      return ($key) ? $recents[$key-1] : $recents;
   }
       
   // -------------------------------------------------------------------- 
   
   function Common_Display($url, $size = 25, $chanopt = false, $descopt = false, $dateopt = false)
   {
      $opened = false;
      $page = "";

      if($this->Atom_Retrieve($url) === false){
        if($this->RSS_Retrieve($url) === false)
          return "$url empty...<br />";
      }
          
      if($size > 0){
        $size += 1; 
        $recents = array_slice($this->Common_Content, 0, $size);
      }    
      
      foreach($recents as $article){
        $type = $article["type"];
        
        if($type == 0){
          if($chanopt != true) continue;
          if($opened == true){
            $page .="</ul>\n";
            $opened = false;
          }
        }
        else{
          if($opened == false && $chanopt == true){
            $page .= "<ul>\n";
            $opened = true;
          }
        }
        
        $title = $article["title"];
        $link = $article["link"];
        $page .= "<".$this->Common_Style."><a href=\"$link\">$title</a>";
        
        if($descopt != false){
          $description = $article["description"];
          if($description != false) $page .= "<br>$description";
            
        }  
          
        if($dateopt != false){            
          $updated = $article["updated"];
          if($updated != false) $page .= "<br /><font $this->Common_Date_Font>$updated</font>";
        }    
        
        $page .= "</".$this->Common_Style.">\n";            
    }

    if($opened == true) $page .="</ul>\n";
    
    return $page."\n";
   }
}
?>