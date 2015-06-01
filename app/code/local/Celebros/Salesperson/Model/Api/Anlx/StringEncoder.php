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
function UUEncode($strString)
{
	$sbCodedString="";
	
	$strString = utf8_encode($strString); //encode the string to UTF8
			for ($i=0;$i<strlen($strString);$i++)
			{
				$c = $strString[$i];
				$intHigh = (128 + 64 + 32 + 16) & ord($c);
				$intLow = (8 + 4 + 2 + 1) & ord($c);				
				$sbCodedString.=chr(($intHigh/16)+65);
				$sbCodedString.=chr($intLow+65);
			}
			return "ZZ" . $sbCodedString;
}

/*
		/// <summary>  FROM .NET
		/// UUEncode - encode a given string
		/// </summary>
		/// <param name="strString"></param>
		/// <returns></returns>
		public static string UUEncode(string strString)
		{
			StringBuilder sbCodedString = new StringBuilder(strString.Length*2);
			byte[] byteArr = System.Text.Encoding.UTF8.GetBytes(strString);//Get UTF8Encoding to byte array from string
			byte b;
			int i,intHigh,intLow;

			for (i=0;i<byteArr.Length;i++)
			{
				b = byteArr[i];
				intHigh = (128 + 64 + 32 + 16) & b;
				intLow = (8 + 4 + 2 + 1) & b;	
				sbCodedString.Append((char)((intHigh/16)+65));
				sbCodedString.Append((char)(intLow+65));
			}
			return  "ZZ"+sbCodedString.ToString();			
		}
*/
?>
