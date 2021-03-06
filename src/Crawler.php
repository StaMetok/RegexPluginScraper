<?php 
namespace plugin\src;

use Composer\Composer;
use Composer\EventDispatcher\Event;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;

class Crawler implements PluginInterface, EventSubscriberInterface{
    protected $urls = array();
    protected $regex;
    protected $matcher;
    protected $interval = 0;
    protected $messages = '';
    protected $verbose = false;
    protected $results = array();
    protected $composer;
    protected $io;

	public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }
	public static function getSubscribedEvents()
    {
        return array(
            PluginEvents::PRE_FILE_DOWNLOAD => array(
                array('onPreFileDownload', 0)
            ),
        );
    }
public function __construct(array $config = array())
    {
        $this->matcher = new Matcher();
		foreach ($config as $item => $value)
        {
            $this->{'set'.ucfirst($item)}($value);
        }
        
    }
    public function setUrls(array $urls)
    {
        foreach ($urls as $url)
        {
            $this->urls[] = $url;
        }
        return $this;
    }
    public function setRegex($regex)
    {
        $this->regex = $regex;

        return $this;
    }
    public function setMatches(array $matches)
    {
        $this->matcher->setMatches($matches);

        return $this;
    }
    public function addMessage($msg, $newLines = 1)
    {
        $this->messages .= $msg;
        for ($i = 0; $i < $newLines; $i++)
        {
            $this->messages .= "\r\n";
        }
    }
    public function getMessages()
    {
        return $this->messages;
    }
    public function get()
    {
        $this->crawl();

        return $this->results;
    }
    public function crawl()
    {
        $i = 0;
        foreach ($this->urls as $url)
        {
            $this->addMessage('Crawling ' . $url);
            //$page = new Page($url);
            //$html = $page->getHTML();
            $html = HtmlDomParser::file_get_html($url);
            if (preg_match_all('/'.$this->regex.'/ms', $html, $matchLines, PREG_SET_ORDER))
            {
                foreach ($matchLines as $matchLine)
                {
                    if ($this->results[$i] = $this->matcher->fetch($matchLine, $url))
                    {
                        $i++;
                    }
                    else
                    {
                        // Remove this match from the data set
                        unset($this->results[$i]);

                        if ($this->matcher->getErrors())
                        {
                            $this->addMessage('On ' . $url);
                            $this->addMessage($this->matcher->getErrors());
                        }
                    }
                }
            }
            else
            {
                $this->addMessage('HTML/Regex is broken on ' . $url);
            }
            $this->afterCrawl();
        }
    }
    protected function afterCrawl()
    {
        if ($this->verbose)
        {
            echo $this->getMessages();
            flush();
            $this->messages = '';
        }
        if ($this->interval > 0) sleep($this->interval);
    }
}
