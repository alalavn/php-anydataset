<?php

namespace ByJG\AnyDataset\Database;

/**
 * Class to create and manipulate Several Data Types
 *
 */
class SQLBind
{
	/**
	 * Each provider have your own model for pass parameter. This method define how each provider name define the parameters
	 *
	 * @param ConnectionManagement $connData
	 * @return string
	 */
	public static function getParamModel(ConnectionManagement $connData)
	{
		if ($connData->getExtraParam("parammodel") != "")
		{
			return $connData->getExtraParam("parammodel");
		}
		elseif ($connData->getDriver() == "sqlrelay")
		{
			return "?";
		}
		else
		{
			return ":_";
		}
	}

	/**
	 * Transform generic parameters [[PARAM]] in a parameter recognized by the provider name based on current DbParameter array.
	 *
	 * @param ConnectionManagement $connData
	 * @param string $sql
	 * @param array $param
	 * @return array An array with the adjusted SQL and PARAMs
	 */
	public static function parseSQL(ConnectionManagement $connData, $sql, $params = null)
	{
		if (is_null($params)) {
            return $sql;
        }

        $paramSubstName = SQLBind::getParamModel ( $connData );
		foreach ( $params as $key => $value )
		{
			$arg = str_replace ( "_", SQLBind::keyAdj ( $key ), $paramSubstName );

            $count = 0;
            $sql = preg_replace("/(\[\[$key\]\]|:" . $key . "[\s\W]|:$key\$)/", $arg . ' ', $sql, -1, $count);
			if ($count === 0) {
                unset($params[$key]);
            }
        }

		$SQL = preg_replace("/\[\[(.*?)\]\]/", "null", $SQL);

		return array($sql, $params);
	}

	public static function keyAdj($key)
	{
		return str_replace ( ".", "_", $key );
	}

}

