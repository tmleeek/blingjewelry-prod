<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality. 
 * If you wish to customize it, please contact Celebros.
 *
 */
//@-; This file was released in build:QANLX V1.6.0049 Date: 9/20/2006 6:32:27 PM @@@
include_once("packetizer.php");
include_once("StringEncoder.php");
include_once("Holders.php");
include_once("DataStructure.php");
include_once("ValidateResult.php");
include_once("DynamicProperty.php");

define("MAX_PACKET_SIZE",800);
define("UNSECURED_PORT",80);
define("SECURED_PORT" ,443);



class LogRequest
		{
		var $DataCollectorIP;
		var $dpCustomProperties;
		var $PublicKeyToken;
		var	$Referrer;

		var	$RequestID;
		var	$CustomerID;
		var $CustomerName;

		// Product members

		var $bUsingQwiserSearch;
		var $sProductName;
	    var $sProductSKU;
		var $sProductVariant;
		var $fProductPrice;
		var $sProductCategory;
		var $iProductQuantity;

		//General memebers

		var $sUserID;
		var $sGroupID;
		var	$sWebSessionID;
		var	$Mode;
		var $SID;
		var $LH;
		var $sCurrentPageURL;
		var $sPreviousPageURL;
		var $iSourceType;
		var $sSourceTypeName;
		var $SearchPhrase;

		// QWACart memebers

		var $sCartID;
		var $sCartCoupon;
		var	$fCartDiscount;
		var	$iCartProductCount;
		var	$fCartSubTotal;

		// checkout members
		var $sOrderID;

		// Product array
		var $ProductsArray;

		// Search result memebers
		var $SpellingCorrectionDriven = false;
		var $CorrectedFrom;

		// Generic search members
		var $strQuery;
		var $strSearchSessionID;
		var $bFromBrowse = false;
		var $bFromQwiser = true;

		// QWAValidateResult memebers
		var $ValidateResult;

		// custom property
		var $m_customProperty;


	function GetLogRequest($LogRequestName)
		{
			$strHttp     = "";
			$iCurrentPort= 0;
			$strLogReq	= "";			// return string with img tags.
			$strRqtID =uniqid(rand(), true);       //????? id of the log Request.
			$RequestData = $this->GetData($LogRequestName);		   // actual data.
			$DataLength	= strlen($RequestData);		// total length of the data.
			$dTimeStamp	= gmdate("U") - 946684800;  // mktime()-946677600; //946684800 <- time from 01/01/1970 till 01/01/2000; 946080000;946634400
			$strAsmVer	= "3.0";

			if ( $this->ValidateLogRequest($LogRequestName) == "Error")
			{
				return "";
			}

			switch ($this->Mode)
			{
				case "plainData":
					$strHttp = "http";
						$iCurrentPort  = UNSECURED_PORT;
					break;
				case "encrypted":
					break;
				case "secured":
					$strHttp = "https";
						$iCurrentPort = SECURED_PORT;
					break;
				default:
					$strHttp = "http";
						$iCurrentPort = UNSECURED_PORT;
					break;
			}

			// packetizer
			 $p = new Packetizer("bySize",MAX_PACKET_SIZE);
			 $splitData = $p->Split($RequestData);

			 $nSplitIdx = 0;

			foreach ($splitData as $packet) // the split method.
			{
				$strLogReq .= "<IMG BORDER='0' NAME='QWISERIMG' WIDTH='1' HEIGHT='1' SRC='" . $strHttp . "://" . $this->DataCollectorIP . ":" . $iCurrentPort . "/QwiserDataCollector/EventListener.aspx";

				// concatenate additional attributes:
				// source identifier

				$strLogReq	.=	"?";

				$strLogReq	.=	"rqt_t=" .   $dTimeStamp		 			//Time when the event was fire
					.	"&amp;sys_sm="	. $p->GetSplitMethod()   //$p->SplitMethod			//split method - byPair | bySize
					.	"&amp;sys_sid="	. $p->SplitID					//split guid
					.	"&amp;sys_sx="	. $nSplitIdx					//Split indeX - index number of the packet
					.	"&amp;sys_stp="	. $p->TotalPackets;			//split total - total number of packets in this split

				$strLogReq	.= "&amp;sys_dz="	. $DataLength;					//request size (data only.)

				$strLogReq	.=	"&amp;ctm_id="	. urlencode($this->CustomerID)	//celebros customer ID
					.   "&amp;ctm_n="	. urlencode($this->CustomerName) // name of the customer
					.	"&amp;ctm_pkt="	. $this->PublicKeyToken 	//Public Key Token for encryption
					.	"&amp;rqt_id="	. $this->GetLogRequestID($LogRequestName) //the id of the request predefine / custom //TODO: add GetId mechanizm
					.	"&amp;rqt_g="	. $this->sGroupID				//Group ID
					.	"&amp;rqt_s="	. $this->sWebSessionID			//Web Session ID
					.	"&amp;rqt_n="	. $this->RqtNmae($LogRequestName)  	//Name of the request. significant only in customProperty
					.	"&amp;rqt_u="	. urlencode($this->sUserID)	//User ID
					.	"&amp;rqt_v="	. $strAsmVer
					.	"&amp;rqt_m="	. $this->iMode($this->Mode) ;				//Mode of the request compressed | unprocessed | enctypted

											//check sum number

				// data section
				// d_dat will hold only split encoded data
				$strLogReq	.=	"&amp;d_dat=" . $packet;					// actual data of the request

				// close tag
				$strLogReq	.= "'>";

				$strLogReq = str_replace("?&","?",$strLogReq);

				$nSplitIdx++;
			}
			return $strLogReq;
		}

		function GetData($LogRequestName)
		{
			switch ($LogRequestName)
			{
				case "LogSearchResult":
					return $this->GetLogSearchResult();
				break;
				case "LogProductDetails":
					return $this->GetLogProductDetails();
				break;
				case "LogAddToCart":
					return $this->GetLogAddToCart();
				break;
				case "LogCheckOut":
					return $this->GetLogCheckOut();
				break;
				case "LogVisitInfo":
					return $this->GetLogVisitInfo();
				break;
				case "LogGenericSearchResult":
					return $this->GetLogGenericSearch();
				break;
				default:
					return $this->GetLogVisitInfo();
			}
		}

		//TODO: add implementation in the dbase to hold all extra properties.
		/// <summary>
		/// Add Custom Properties to the log.
		/// </summary>
		/// <param name="strName">name of the new parameter</param>
		/// <param name="strValue">value of the new parameter</param>
		function AddCustomProperty($strName, $strValue)
		{
			if ($this->m_customProperty == null )
				$this->m_customProperty = new DynamicProperty();

			$name = "cd_" . $strName;
			$this->m_customProperty->SetProperty($name, $strValue);
		}

		function GetCustomData()
		{
			// data in override implementation should be serialized with the string encoder to
			// prevent char problems and security issues.
			//... for additional data don't forget the & delimiter between pairs.

			if ($this->m_customProperty == null )
				return "";

			$strProperties = $this->m_customProperty->BuildString();
			return UUEncode($strProperties);
		}


		function GetLogProductDetails()
		{
			$strCustomData = $this->GetCustomData();
			$strData = "";
			$strProductDetails ="";
			$strReferrerData = $this->GetReferrerData();

			//sProduct p = new sProduct();
			$Prod = new ProductDetailsHolder();

			//$value = trim($value);
			if($this->iProductQuantity==null || $this->iProductQuantity=="" || (trim($this->iProductQuantity))=="")
				$this->iProductQuantity=1;
			if( $this->fProductPrice==null ||  $this->fProductPrice=="" || (trim($this->fProductPrice))=="")
				 $this->fProductPrice=0;

			$Prod->ProductList[0]["SKU"]		= $this->sProductSKU;
			$Prod->ProductList[0]["Variant"]	= $this->sProductVariant;
			$Prod->ProductList[0]["Name"]		= $this->sProductName;
			$Prod->ProductList[0]["Price"]		= $this->fProductPrice;
			$Prod->ProductList[0]["Category"]	= $this->sProductCategory;
			$Prod->ProductList[0]["Quantity"]	= $this->iProductQuantity;


			$strProductDetails =$Prod->ToString();
			if (!($strProductDetails=="") || (trim($this->fProductPrice))=="")
				$strData	.= "0d_pd=" . $strProductDetails;

			if ( $this->bUsingQwiserSearch == true )
			{
				if (!($this->SID == null || $this->SID=="" || (trim($this->SID)) == "" ))
					$strData	.= "0d_sid=" . UUEncode($this->SID);
			}
			else
			{
				$strData	.= "0d_sid=" . UUEncode($this->sWebSessionID);
			}

			if (!($strReferrerData == null || $strReferrerData=="" || (trim($strReferrerData)) == "" ))
				$strData	.= "0d_ref=" . $strReferrerData;

			if (!( $strCustomData == "" || (trim($strCustomData)) == "" ))
				$strData .= "0d_cd=" . $strCustomData;

			return $strData;
		}

		function GetLogSearchResult()
		{
			$strCustomData = $this->GetCustomData();
			$strData = "";
			$strReferrerData = $this->GetReferrerData();

			if (!($strReferrerData == null || $strReferrerData=="" || (trim($strReferrerData))==""))
				$strData	.= "0d_ref=" . $strReferrerData;

			if (!($this->SID == null || $this->SID == "" || (trim($this->SID)) == "" ))
				$strData	.= "0d_ssid=" . UUencode($this->SID);

			if (!($this->LH == null || $this->LH == "" || (trim($this->LH)) == "" ))
				$strData	.= "0d_lh=" . $this->LH;

			$strData	.= "0d_fq=" . $this->BoolToString($this->bFromQwiser);


			$ADSearchHolder = new SRAdditionalHolder();
			$ADSearchHolder->SpellingCorrectionDriven	= $this->BoolToString($this->SpellingCorrectionDriven);
			$ADSearchHolder->CorrectedFrom				= $this->CorrectedFrom;
			$ADSearchHolder->FromBrowse					= $this->BoolToString($this->bFromBrowse);

			$strADSearchInfo = $ADSearchHolder->ToString();
			if (!($strADSearchInfo=="" || (trim($strADSearchInfo)) == "" ))
				$strData	.= "0d_sr=" . $strADSearchInfo;

		//			SRAdditionalHolder	SRHolder = new SRAdditionalHolder();
		//			SRHolder.SpellingCorrectionDriven = m_bSpellingCorrectionDriven;
		//			SRHolder.CorrectedFrom = m_strCorrectedFrom;
		//			SRHolder.FromBrowse = m_bFromBrowse;
		//			string strSRAdditional = SRHolder.ToString();
		//
		//			if (!String.Empty.Equals(strSRAdditional))
		//				strData += "0d_sr=" + strSRAdditional;
		//
		if (!($strCustomData == "" || (trim($strCustomData)) == "" ))
				$strData .= "0d_cd=" . $strCustomData;

					return $strData;
		}

		function GetLogGenericSearch()
		{
			$strCustomData = $this->GetCustomData();
			$strData = "";
			$strGenericInfo = "";
			$strReferrerData = $this->GetReferrerData();

			if (!($strReferrerData == null || $strReferrerData=="" || (trim($strReferrerData)) == ""  ))
				$strData	.= "0d_ref=" . $strReferrerData;

			$GenericHolder = new GenericSRHolder();
			$GenericHolder->SearchSession = $this->strSearchSessionID;
			$GenericHolder->Query			= $this->strQuery;
			$GenericHolder->FromBrowse		= $this->BoolToString($this->bFromBrowse);

			$strGenericInfo = $GenericHolder->ToString();
			if (!($strGenericInfo=="" || (trim($strGenericInfo)) == "" ))
				$strData	.= "0d_gsr=" . $strGenericInfo;

			if (!($strCustomData == "" || (trim($strCustomData)) == "" ))
				$strData .= "0d_cd=" . $strCustomData;

			return $strData;
		}


		function GetLogAddToCart()
		{
			$strCustomData = $this->GetCustomData();
			$strData	= "";
			$strCartInfo = "";
			$strProductDetails = "";
			$strReferrerData = $this->GetReferrerData();

			$Prod = new ProductDetailsHolder();

			if($this->iProductQuantity==null || $this->iProductQuantity=="" || (trim($this->iProductQuantity)) == "" )
				$this->iProductQuantity=1;

			$Prod->ProductList[0]["SKU"]		= $this->sProductSKU;
			$Prod->ProductList[0]["Variant"]	= $this->sProductVariant;
			$Prod->ProductList[0]["Name"]		= $this->sProductName;
			$Prod->ProductList[0]["Price"]		= $this->fProductPrice;
			$Prod->ProductList[0]["Category"]	= $this->sProductCategory;
			$Prod->ProductList[0]["Quantity"]	= $this->iProductQuantity;

			$strProductDetails = $Prod->ToString();
			if (!($strProductDetails=="" || (trim($strProductDetails)) == "" ))
				$strData	.= "0d_pd=" . $strProductDetails;

			$cartHolder = new CartInfoHolder();
			$cartHolder->CartID		= $this->sCartID;
			$cartHolder->Coupon		= $this->sCartCoupon;
			$cartHolder->Discount		= $this->fCartDiscount;
			$cartHolder->ProductCount = $this->iCartProductCount;
			$cartHolder->SubTotal		= $this->fCartSubTotal;

			$strCartInfo = $cartHolder->ToString();
			if (!($strCartInfo=="" || (trim($strCartInfo)) == ""))
				$strData	.= "0d_ci=" . $strCartInfo;

			if ( $this->bUsingQwiserSearch == true )
			{
				if (!( $this->SID == null || $this->SID=="" || (trim($this->SID)) == "" ))
					$strData	.= "0d_sid=" . UUEncode($this->SID);
			}
			else
			{
				$strData	.= "0d_sid=" . UUEncode($this->sWebSessionID);
			}
			//new code: Ignoring the bUsingQwiserSearch parameter
			/*
			if (!( $this->SID == null || $this->SID=="" || (trim($this->SID)) == "" ))
				$strData	.= "0d_sid=" . UUEncode($this->SID);
			else
				$strData	.= "0d_sid=" . UUEncode($this->sWebSessionID);
			*/

			if (!($strReferrerData == null || $strReferrerData=="" || (trim($strReferrerData)) == "" ))
				$strData	.= "0d_ref=" . $strReferrerData;

			if (!($strCustomData == "" || (trim($strCustomData)) == "" ))
				$strData .= "0d_cd=" . $strCustomData;

			return $strData;
		}


		function GetLogCheckOut()
		{
			$strCustomData = $this->GetCustomData();
			$strData	= "";
			$strReferrerData = $this->GetReferrerData();

			// fill all products
			$Prod = new ProductCOHolder();			
			if(count($this->ProductsArray)>0)
			{
				for($i=0;$i<count($this->ProductsArray);$i++)
				{
					$Prod->ProductList[$i]["SKU"]		= $this->ProductsArray[$i]["SKU"];
					$Prod->ProductList[$i]["Variant"]	= $this->ProductsArray[$i]["Variant"];
					$Prod->ProductList[$i]["Discount"]	= $this->ProductsArray[$i]["Discount"];
					$Prod->ProductList[$i]["Price"]		= $this->ProductsArray[$i]["Price"];
					$Prod->ProductList[$i]["Quantity"]	= $this->ProductsArray[$i]["Quantity"];
					$Prod->ProductList[$i]["Coupon"]	= $this->ProductsArray[$i]["Coupon"];
				}
			}
			$strProductDetails = $Prod->ToString();

			if (!($strProductDetails=="" || (trim($strProductDetails)) == ""))
				$strData	 .= "0d_pdco=" . $strProductDetails;

			// fill cart info
			$cartHolder = new CartInfoHolder();
			$cartHolder->CartID		= $this->sCartID;
			$cartHolder->Coupon		= $this->sCartCoupon;
			$cartHolder->Discount		= $this->fCartDiscount;
			$cartHolder->ProductCount = $this->iCartProductCount;
			$cartHolder->SubTotal		= $this->fCartSubTotal;

			$strCartInfo = $cartHolder->ToString();

			if (!($strCartInfo=="" || (trim($strCartInfo)) == ""))
				$strData	.= "0d_ci=" . $strCartInfo;

			if (!($this->sOrderID=="" || (trim($this->sOrderID)) == "" ))
				$strData	.= "0d_oid=" . UUEncode($this->sOrderID);


			if (!( $strReferrerData == null || $strReferrerData=="" || (trim($strReferrerData)) == "" ))
				$strData	.= "0d_ref=" . $strReferrerData;

			if (!($strCustomData == "" || (trim($strCustomData)) == ""))
				$strData .= "0d_cd=" . $strCustomData;

			return $strData;
		}


		function GetLogVisitInfo()
		{
			$strCustomData = $this->GetCustomData();
			$strData = "";
			$strReferrerData = $this->GetReferrerData();
			$PageInfo = new PageInfoHolder();

			if (!($strReferrerData == null || $strReferrerData=="" || (trim($strReferrerData)) == "" ))
				$strData	.= "0d_ref=" . $strReferrerData;

			$PageInfo->Name  = $this->Name;
			$PageInfo->Url  =  $this->sCurrentPageURL;
			$PageInfo->Category = $this->Category;

			$strPage = $PageInfo->ToString();

			if (!($strPage == null || $strPage=="" || (trim($strPage)) == "" ))
				$strData	.= "0d_page=" . $strPage;

			//... for additional data  don't forget the & between pairs.*/

			if (!($strCustomData == "" || (trim($strCustomData)) == "" ))
				$strData .= "0d_cd=" . $strCustomData;

			return $strData;
		}

		function ValidateLogRequest($LogRequestName)
		{
			$Message = "";

			$this->ValidateResult = new sValidateResult();

			// error message
			$iSeverity = "Error";

			// customer error
			if ( $this->CustomerID == null || $this->CustomerID == "" || (trim($this->CustomerID)) == "" )
			{
				$Message = "evrErrCustomerIDIsMissing";
			}

			// data collector errors
			elseif ( $this->DataCollectorIP == null || $this->DataCollectorIP == "" || (trim($this->DataCollectorIP)) == "")
			{
				$Message = "evrErrServerNameIsMissing";
			}

			if ( $Message == "" )
			{

				switch ($LogRequestName)
				{
					case "LogSearchResult":
						$Message = GetSRValidateResult($this->SID, $this->LH);
						break;
					case "LogGenericSearchResult":
						$Message = GetGRValidateResult($this->strSearchSessionID);
						break;
					case "LogProductDetails":
						$Message = GetPDValidateResult($this->SID, $this->sProductSKU);
						break;
					case "LogAddToCart":
						$Message = GetATCValidateResult($this->SID, $this->sProductSKU, $this->sCartID);
						break;
					case "LogCheckOut":
						$Message = GetCOValidateResult($this->sCartID, $this->sOrderID);
						break;
					default:
						$Message = "";
				}
			}

			if ($Message == "")
			{

				$iSeverity = "Warning";

				/*if ( (DataCollector.Port == UNSECURED_PORT) && (m_Mode == enumRequestMode.secured) )
				{
					sCurrValidateRes.MessageID = enumValidateResult.evrWrnConflictInDCParam;
					return sCurrValidateRes;
				}*/
				if ( $this->CustomerName == null || $this->CustomerName = "" || (trim($this->CustomerName)) == "")
				{
					$Message = "evrWrnCustomerNameISMissing";

				}
			}

			if ($Message == "" )
			{
				$iSeverity = "RequestOK";
			}

			$this->ValidateResult->iSeverity  = $iSeverity;
			$this->ValidateResult->strMessage = $Message;

			return $iSeverity;

		}

		function iMode($Mode)
		{
			switch ($Mode)
			{
				case "plainData":
						return 0;
				case "encrypted":
						return 1;
				case "secured":
					    return 2;
				default:
						return -1;
			}
		}

		function RqtNmae($LogRequestName)
		{
			switch ($LogRequestName)
			{
				CASE "LogSearchResult":
						return "sr";
				CASE "LogProductDetails":
						return "PD";
				CASE "LogAddToCart":
						return "ToCRT";
				CASE "LogCheckOut":
						return "CkOut";
				CASE "LogVisitInfo":
						return "VisitInfo";
				case "LogGenericSearchResult":
						return "GSR";
				default:
						return -1;//TODO
			}
		}

		function GetLogRequestID($LogRequestName)
		{
				switch ($LogRequestName)
			{
				CASE "LogSearchResult":
						return "1";
				CASE "LogProductDetails":
						return "2";
				CASE "LogAddToCart":
						return "3";
				CASE "LogCheckOut":
						return "4";
				CASE "LogVisitInfo":
						return "5";
				CASE "LogGenericSearchResult":
						return "6";
				default:
						return -1;//TODO
			}
		}


		function  GetReferrerData()
		{
			$RefInfo = new ReferrerInfoHolder();

			$RefInfo->ReferrerUrl = $this->sPreviousPageURL;
			$RefInfo->CampaignName = $this->sSourceTypeName;
			$RefInfo->SearchPhrase = $this->SearchPhrase;
			$RefInfo->CampaignType = $this->iSourceType;

			return $RefInfo->ToString();
		}

		function GetValidateResult()
		{
			return $this->ValidateResult;
		}

		function BoolToString($bool)
		{
			if ($bool == 1 )
				return "True";
			else
				return "False";
		}
    }


?>
