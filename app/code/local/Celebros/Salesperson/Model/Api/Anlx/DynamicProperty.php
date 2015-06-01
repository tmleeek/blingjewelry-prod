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

define('DEFAULT_DELIMITER',"&");

class DynamicProperty
{

	var $m_properties;
	//private Hashtable	m_properties = new Hashtable();

		// constructors
		function DynamicProperty()
		{
			$m_properties = array();
		}


		function SetDPWithProp($strProp)//:this(strProp,DEFAULT_DELIMITER)
		{
			$this->SetDPWIthDelimiter($strProp, DEFAULT_DELIMITER);
		}


		function SetDPWIthDelimiter($strProp,$strDelimit)
		{
			$Prop = $strProp;
			//m_properties.Clear();
			$m_properties = array();

			//if (Prop.StartsWith(strDelimit))
			if (strchr($Prop, $strDelimit) == 0 )
			{
				$start = strlen($strDelimit);
				$end = strlen($Prop);
				$Prop = substr($Prop, $start, $end-$start);
			}

			$parts[] = split($strDelimit, $Prop);

			foreach ($parts as $pairs)
			{
				$pair[] = split("=", $pairs, 2);
				if ( count($pair) == 2 )
				{
					$this->m_properties[$pair[0]] = $pair[1];
				}
			}
		}

		/*function SetDynamicProperty(DynamicProperty dynamicProp)
		{
			Hashtable properties = dynamicProp.GetAllProperties();

			m_properties.Clear();
			// Enumerate properties and create report server specific string.
			IDictionaryEnumerator customPropEnumerator = properties.GetEnumerator();
			while ( customPropEnumerator.MoveNext() )
			{
				m_properties.Add(customPropEnumerator.Key,customPropEnumerator.Value);
			}
		}*/



		/// <summary>
		/// create string that hold all parameters delimit by delimit parameter
		/// </summary>
		/// <param name="strDelimit">string seperator between pairs.</param>
		/// <returns>string with all parameters.</returns>
		function BuildStringWithDel($strDelimit)
		{
			$strParameters = $this->EmumProperties($this->m_properties,$strDelimit);
			return $strParameters;
		}

		#endregion

		#region Methods

		/// <summary>
		/// Enumerate Hashtable and create report server access specific string.
		/// </summary>
		/// <param name="properties">hash table of all properties</param>
		/// <param name="strDelimiter">string use to delimiter between pairs. (empty string will become ampersand)</param>
		/// <returns></returns>
		//function EmumProperties(Hashtable properties,$strDelimiter)
		function EmumProperties($properties,$strDelimiter)
		{
			$paramsString = "";
			if ($strDelimiter == "")
				$strDelimiter = DEFAULT_DELIMITER;

			// Enumerate properties and create report server specific string.
			//IDictionaryEnumerator customPropEnumerator = properties.GetEnumerator();
			//$customPropEnumerator = $properties.GetEnumerator();

			/*while (list($key,$value) = each($goodfoodArray)) {
				echo "$key : $value<br>";*/

			if ($properties != null)
			{
				$key = array_keys($properties);
				foreach ($key as $value)
				{
					$paramsString .= $strDelimiter
						. $value . "=" . $properties[$value];
				}
			}
			return $paramsString;
		}

		/// <summary>
		/// get all the parameters
		/// </summary>
		/// <returns>Hashtable that hold all the parameters in the DP object</returns>
		/*public Hashtable GetAllProperties()
		{
			return m_properties;
		}*/

		/// <summary>
		/// Add or remove url access string properties.
		/// </summary>
		/// <param name="name"></param>
		/// <param name="value"></param>
		function SetProperty($name, $value)
		{
			//try
			//{
				// Remove if value is null or empty. Value is null of the property grid value
				// is null or empty. Empty or null removes the property from the Hashtable.
				if($value == null || $value == "" )
				{
					if ($name != null && $name != "")
					{
						$bFound = false;
						in_array($name, $bFound);
						if ($bFound)
							$this->m_properties.Remove($name);
					}
				}
				else
				{
					if ($this->m_properties != null)
					{
						if(	array_key_exists($name, $this->m_properties) )
						{
							// Change if key exists
							$this->m_properties[$name] = $value;
						}
						else
						{
							// Add if key does not exist
							$this->m_properties[$name] = $value;
						}
					}
				}
				// Build a new string with all the parameters as pairs.
				$this->BuildString();
			/*
			}
			catch(Exception ex)
			{
				// throws the exception to the client
				throw ex;
			}*/
		}

		// create string that hold all parameters delimit by ampersand
		function BuildString()
		{
			$strParameters = $this->EmumProperties($this->m_properties,"");
			return $strParameters;
		}

	}

?>

