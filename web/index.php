<?php
/***********************************************************************/
/*   Copyright Mentor Graphics Corporation 2014 All Rights Reserved.   */
/*                                                                     */
/*     THIS WORK CONTAINS TRADE SECRET AND PROPRIETARY INFORMATION     */
/*     WHICH IS THE PROPERTY OF MENTOR GRAPHICS CORPORATION OR ITS     */
/*             LICENSORS AND IS SUBJECT TO LICENSE TERMS.              */
/***********************************************************************/

// Loads the pdf.js viewer.html file and modifies the head section with the appropriate .js file includes (some dynamically fetched) to use it in our system

$buildCheckPath = getcwd() . "/../../sdm/web/sites/pm/www/public/js/dojoReleasePm/";

$doc = new DOMDocument();
@$doc->loadHTML(file_get_contents('./viewer.html'));

function getServer()
{
   return strtolower(array_shift(explode("/",$_SERVER['SERVER_PROTOCOL'])))."://".$_SERVER['SERVER_NAME'];
}

function getInfo()
{
   $content = file_get_contents(getServer() . '/info/general');
   $contentAsObj = json_decode($content);
   return $contentAsObj;
}

function getWsPort()
{
   $info = getInfo();
   return $info->heartbeatParams->clientPortWs;
}

foreach($doc->childNodes as $item)
{
   if($item->tagName != 'html')
      continue;
   
   foreach($item->childNodes as $htmlChildNode)
   {
      if($htmlChildNode->tagName != 'head')
         continue;
      
      $headNode = $htmlChildNode;

      $pNewSocketIoNode = $doc->createElement('script');
      $pNewSocketIoNode->setAttribute("type", "text/javascript");
      $pNewSocketIoNode->setAttribute("src", getServer() . ":" . getWsPort() . "/socket.io/socket.io.js");
      $headNode->appendChild($pNewSocketIoNode);

      $dojoPath = "/js/dojo";
      if(is_dir($buildCheckPath))
         $dojoPath = "/js/dojoReleasePm";

      $pNewSdmAddonsNode = $doc->createElement('script');
      // Add any tags that are needed here
      $pNewDojoNode = $doc->createElement('script', 'dojoConfig= {
        async: true,
        paths: {
         "dojo": "' . $dojoPath . '/dojo",
         "dojox": "' . $dojoPath . '/dojox",
         "dijit": "' . $dojoPath . '/dijit",
         "pmApi": "' . $dojoPath . '/pmApi",
         "pm": "' . $dojoPath . '/pm",
         "noBusinessBase": "' . $dojoPath . '/noBusinessBase",
         "noBusinessOslc": "' . $dojoPath . '/noBusinessOslc",
         "dgrid": "' . $dojoPath . '/dgrid",
         "put-selector": "' . $dojoPath . '/put-selector",
         "xstyle": "' . $dojoPath . '/xstyle",
         "dojoReleasePm": "' . $dojoPath . '"
        }
      };
      var DOJO_FRAMEWORK_PATH = "' . $dojoPath . '";');
      $headNode->appendChild($pNewDojoNode);

      $pNewDojoSrcNode = $doc->createElement('script');
      $pNewDojoSrcNode->setAttribute("type", "text/javascript");
      $pNewDojoSrcNode->setAttribute("src", $dojoPath . "/dojo/dojo.js");
      $headNode->appendChild($pNewDojoSrcNode);
      
      $pNewSdmAddonsNode->setAttribute("type", "text/javascript");
      $pNewSdmAddonsNode->setAttribute("src", "../sdmAddons/sdm.js");
      $headNode->appendChild($pNewSdmAddonsNode);
   }
}

// At the end, dump out the modified HTML
echo $doc->saveHTML();
