<?php
namespace plugin\src;

class Matcher {
    protected $matches = array();
    protected $errors = '';
    protected $filter = null;
    /**
     * Create a new Matcher instance.
     */
    function __construct($matches = array(), $filter = null)
    {
        $this->setMatches($matches);
        $this->setFilter($filter);
    }
    /**
     * Set the matches
     */
    public function setMatches(array $matches)
    {
        $this->matches = $matches;

        return $this;
    }
    /**
     * Set the filter to be applied to the matches
     */
    public function setFilter($filter = null)
    {
        $this->filter = is_callable($filter) ? $filter : null;

        return $this;
    }
    public function getErrors()
    {
        return $this->errors;
    }
    /**
     * Fetch the values from a match
     */
    public function fetch(array $data, $url = '')
    {
        $result = array();
        $this->errors = '';
        $dataRules = array();

        foreach ($this->matches as $match)
        {
            // Get the match value, optionally apply a function to it
            if (isset($match['apply']))
            {
                $result[$match['name']] = $match['apply']($data[$match['id']], $url);
            }
            else
            {
                $result[$match['name']] = $data[$match['id']];
            }   
        }
        return $result;
    }
}