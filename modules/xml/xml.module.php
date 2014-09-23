<?php
    class xml_module
    {
        public function __construct()
        {   
            $this->opus =& opus::$instance;
            $this->url_accessible = array('sitemap', 'sitemap_gzip');

            $this->standard_priority = "0.5";
            $this->standard_freq = "weekly";

            $this->pages = array(
                    array('loc' => 'movies', 'changefreq' => 'daily', 'priority' => '1'),
                    array('loc' => 'movies/create')
            );
        }

        public function sitemap()
        {
            header('Content-type: text/xml');
            echo $this->create_sitemap($this->pages);

            if ($this->opus->load->is_module_loaded('log')) { $this->opus->log->write('info', 'Generated sitemap.xml by IP ' . $_SERVER['REMOTE_ADDR']); }
        }

        public function sitemap_gzip()
        {
            $compressed_sitemap = gzencode($this->create_sitemap($this->pages));

            header("Content-type: application/zip");
            header("Content-Transfer-Encoding: Binary");
            header("Content-length: " . strlen($compressed_sitemap));
            header("Content-disposition: attachment; filename=\"sitemap.xml.gz\"");

            echo $compressed_sitemap;

            if ($this->opus->load->is_module_loaded('log')) { $this->opus->log->write('info', 'Generated sitemap.xml.gz by IP ' . $_SERVER['REMOTE_ADDR']); }
        }

        private function create_sitemap($data) 
        {
            $root = '<?xml version="1.0" encoding="UTF-8"?>
                        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
                                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
                                http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"/>
                        ';
            $xml = new SimpleXMLElement($root);

            foreach ($data as $page)
            {
                if (!$page['loc'])
                    return;

                $page['changefreq'] = (isset($page['changefreq'])) ? $page['changefreq'] : $this->standard_freq;
                $page['priority'] = (isset($page['priority'])) ? $page['priority'] : $this->standard_priority;

                $page['loc'] = $this->opus->config->base_url($page['loc']);

                $url = $xml->addChild('url');
                $url->addChild('loc', $page['loc']);
                $url->addChild('changefreq', $page['changefreq']);
                $url->addChild('priority', $page['priority']);
            }

            return $xml->asXML();
        }
    }
?>