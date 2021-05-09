<?php

namespace App\Database;

use Illuminate\Database\Eloquent\Model;

use SimpleXMLElement;

abstract class AbstractModel extends Model
{
    /**
     * Dump the current SQL and bindings.
     *
     * @return $this
     */
    public function dump()
    {
        dump($this->toSql(), $this->getBindings());

        return $this;
    }

    /**
     * Die and dump the current SQL and bindings.
     *
     * @return void
     */
    public function dd()
    {
        dd($this->toSql(), $this->getBindings());
    }

     /**
     * Return XML formated data for an entry
     * @return string
     */
    public function toXML()
    {
        $xml = new SimpleXMLElement('<' . array_pop(explode('\\', get_class($this))) . '/>');
        foreach ($this->first()->attributes as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item' . $key; //dealing with <0/>..<n/> issues
            }
            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                $this->array_to_xml($value, $subnode);
            } else {
                $xml->addChild("$key", htmlspecialchars("$value"));
            }
        }
        return $xml->asXML();
    }
}
