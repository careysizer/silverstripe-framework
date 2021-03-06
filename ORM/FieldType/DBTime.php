<?php

namespace SilverStripe\ORM\FieldType;

use Convert;
use Zend_Date;
use TimeField;
use SilverStripe\ORM\DB;

/**
 * Represents a column in the database with the type 'Time'.
 *
 * Example definition via {@link DataObject::$db}:
 * <code>
 * static $db = array(
 * 	"StartTime" => "Time",
 * );
 * </code>
 *
 * @todo Add localization support, see http://open.silverstripe.com/ticket/2931
 *
 * @package framework
 * @subpackage orm
 */
class DBTime extends DBField {

	/**
	 * @config
	 * @see Date::nice_format
	 * @see DBDateTime::nice_format
	 */
	private static $nice_format = 'g:ia';

	public function setValue($value, $record = null, $markChanged = true) {
		if($value) {
			if(preg_match( '/(\d{1,2})[:.](\d{2})([a|A|p|P|][m|M])/', $value, $match )) $this->TwelveHour( $match );
			else $this->value = date('H:i:s', strtotime($value));
		} else {
			$value = null;
		}
	}

	/**
	 * Returns the time in the format specified by the config value nice_format, or 12 hour format by default
	 * e.g. "3:15pm"
	 *
	 * @return string
	 */
	public function Nice() {
		return $this->Format($this->config()->nice_format);
	}

	/**
	 * Return a user friendly format for time
	 * in a 24 hour format.
	 *
	 * @return string Time in 24 hour format
	 */
	public function Nice24() {
		return $this->Format('H:i');
	}

	/**
	 * Return the time using a particular formatting string.
	 *
	 * @param string $format Format code string. e.g. "g:ia"
	 * @return string The date in the requested format
	 */
	public function Format($format) {
		if($this->value) {
			return date($format, strtotime($this->value));
		}
		return null;
	}

	public function TwelveHour( $parts ) {
		$hour = $parts[1];
		$min = $parts[2];
		$half = $parts[3];

		// the transmation should exclude 12:00pm ~ 12:59pm
		$this->value = (( (strtolower($half) == 'pm') && $hour != '12') ? $hour + 12 : $hour ) .":$min:00";
	}

	public function requireField() {
		$parts=Array('datatype'=>'time', 'arrayValue'=>$this->arrayValue);
		$values=Array('type'=>'time', 'parts'=>$parts);
		DB::require_field($this->tableName, $this->name, $values);
	}

	public function scaffoldFormField($title = null, $params = null) {
		$field = TimeField::create($this->name, $title);

		// Show formatting hints for better usability
		$field->setDescription(sprintf(
			_t('FormField.Example', 'e.g. %s', 'Example format'),
			Convert::raw2xml(Zend_Date::now()->toString($field->getConfig('timeformat')))
		));
		$field->setAttribute('placeholder', $field->getConfig('timeformat'));

		return $field;
	}

}
