<?php

# Copyright (c)  2012 - <mlunzena@uos.de>
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.

class StudipImpress extends StudipPlugin implements StandardPlugin
{
    function __construct()
    {
        parent::__construct();

        NotificationCenter::addObserver($this,
                                        'onActivateItem',
                                        'NavigationDidActivateItem',
                                        '/course/wiki/show');
    }

    function onActivateItem($event, $subject, $user_data)
    {
        if ($this->isActivated($this->getContext())) {

            $this->addImpressButton();
            $this->addImpressMarkup();

            PageLayout::addStylesheet($this->getPluginUrl() . '/css/markup.css');
        }
    }

    function getContext()
    {
        return $GLOBALS['SessSemName'][1];
    }

    function addImpressButton()
    {
        $keyword = Request::get("keyword", "WikiWikiWeb");
        $version = Request::get("version", "");
        $url = PluginEngine::getURL($this,
                                    compact("keyword", "version"),
                                    'show');

        $course_nav = new Navigation('Preso²');
        $course_nav->setURL($url);
        Navigation::addItem('/course/wiki/impress', $course_nav);
    }

    function addImpressMarkup()
    {
            StudipFormat::addStudipMarkup('impress-meta',
                                          '\{:(\w[-\w]*):',
                                          ':\}',
                                          'StudipImpress::markupImpressMeta');
           # StudipFormat::addStudipMarkup('impress-steps',
           #                               '^@STEP([^\n]*)\n?', NULL, 'StudipImpress::markupImpressStep');

            StudipFormat::addStudipMarkup('impress-steps2',
                                          '\[step(.*?)?\]',
                                          '\[\/step\]',
                                          'StudipImpress::markupImpressStep2');
    }

    static function markupImpressMeta($markup, $matches, $contents)
    {
        return "<div class='meta'>" . htmlReady($matches[1]) . ": ". htmlReady($contents) . "</div>";
    }

    static function markupImpressStep($markup, $matches, $contents)
    {
        return sprintf('<div class="impress-step">STEP %s</div>', $matches[1]);
    }

    static function markupImpressStep2($markup, $matches, $contents)
    {
        return sprintf('<div class="impress-step">STEP %s <hr> %s</div>', @$matches[1], $contents);
    }

    function show_action()
    {
        # check permission
        if (!$GLOBALS['perm']->have_studip_perm("user", $this->getContext())) {
            throw new AccessDeniedException("Access denied");
        }

        # get wiki page
        require_once "lib/wiki.inc.php";
        $page = getWikiPage(Request::option("keyword"), Request::option("version", ""));


        $page['meta'] = $this->extractMetaData($page);

        # generate slides from wiki markup
        $tree = $this->_extractSlides($page);

        if (!$this->hasOptions($tree) && !isset($page['meta']['template'])) {
            $page['meta']['template'] = 'simple';
        }

        $page['slides'] = $tree;

        # render slides
        $factory = new Flexi_TemplateFactory(dirname(__FILE__) . '/templates/');
        echo $factory->render("index", array('plugin' => $this, 'page' => $page));
    }

    function extractMetaData($page)
    {
        $meta = array();
        $result = preg_match_all('/\{:(\w[-\w]*):(.*?):\}/', $page['body'], $matches, PREG_SET_ORDER);
        if ($result) {
            foreach ($matches as $match) {
                list($_, $key, $value) = $match;
                $meta[$key] = $value;
            }
        }
        return $meta;
    }
    function _extractSlides($page)
    {
        $body = wikiReady($page['body'], FALSE);
        $result = preg_match_all('/^(@STEP.*)$/m', $body, $matches,
                                 PREG_OFFSET_CAPTURE);

        $slides = array();
        for ($i = 0, $offsets = $matches[0]; $i < sizeof($offsets); ++$i) {
            $next    = @$offsets[$i + 1][1] ?: strlen($body);
            $divider = $offsets[$i][0];
            $offset  = $offsets[$i][1] + strlen($divider) + 1;

            $slides[] = array(
                "options" => $this->extractOptions($divider),
                "text"    => substr($body, $offset, $next - $offset)
            );
        }

        return $slides;
    }

    function extractSlides($page)
    {
        $tokens = $this->tokenizePage($page['body']);
        $slides = $this->parseTokens($tokens);

        return $slides;
    }

    function tokenizePage($page)
    {
        $tokens = array();
        $pos = 0;
        $pattern = '/(?P<start>\[step(?P<options>.*?)?\])|(?P<end>\[\/step\])/';
        while (preg_match($pattern, $page, $match, PREG_OFFSET_CAPTURE, $pos)) {

            # collect non-matched text
            $text = substr($page, $pos, $match[0][1] - $pos);
            if (!preg_match('/^\s*$/', $text)) {
                $tokens[] = array('TEXT', $text);
            }

            # and update position
            $pos = $match[0][1];

            # add start token
            if (@$match['start'][1] > -1) {
                $tokens[] = array('START', $this->extractOptions($match['options'][0]));
            }

            # ... or end token
            else if  (@$match['end'][1] > -1) {
                $tokens[] = array('END');
            }

            # jump behind match
            $pos += strlen($match[0][0]);
        }

        # text remaining?
        if ($pos < strlen($page) - 1) {
            $tokens[] = array('TEXT', substr($page, $pos));
        }

        return $tokens;
    }

    function parseTokens($tokens, $node = NULL)
    {
        while (list($key, $token) = each($tokens)) {

            if ('TEXT' === $token[0]) {
                if ($node) {
                    $node['text'] .= $token[1];
                }
            }

            else if ('START' === $token[0]) {
                $step = array('text' => '', 'options' => $token[1]);
                $this->parseTokens(&$tokens, &$step);
                $node['slides'][] = $step;
            }

            else if ('END' === $token[0]) {
                return $node;
            }
        }
        return $node;
    }

    function extractOptions($string)
    {
        $attributes = array('class' => 'step');

        foreach ($this->splitStringByWhitespace($string) as $option) {

            # option is a property => data-* attributes
            if (preg_match('/^([\w-]+)=([\w-.]+)$/', $option, $matches)) {
                $attributes[$matches[1]] = htmlReady($matches[2]);
            }

            # option starts with a dot => additional class
            else if (@$option[0] === '.') {
                $attributes['class'] .= " " . htmlReady(substr($option, 1));
            }

            # option starts with a hash => id attribute
            else if (@$option[0] === '#') {
                $attributes['id'] = htmlReady(substr($option, 1));
            }
        }

        return $attributes;
    }

    function splitStringByWhitespace($string)
    {
        return preg_split('/[[:space:]]/', $string, -1, PREG_SPLIT_NO_EMPTY);
    }

    function hasOptions($slides)
    {
        foreach ($slides as $slide) {
            # TODO böser check
            if ($slide['options'] != array('class' => 'step')) {
                return true;
            }
        }
        return false;
    }


   /**
     * Return a navigation object representing this plugin in the
     * course overview table or return NULL if you want to display
     * no icon for this plugin (or course). The navigation object's
     * title will not be shown, only the image (and its associated
     * attributes like 'title') and the URL are actually used.
     *
     * By convention, new or changed plugin content is indicated
     * by a different icon and a corresponding tooltip.
     *
     * @param  string   course or institute range id
     * @param  int      time of user's last visit
     *
     * @return object   navigation item to render or NULL
     */
    function getIconNavigation($course_id, $last_visit)
    {
        # no icon required
        return NULL;
    }

    /**
     * Return a template (an instance of the Flexi_Template class)
     * to be rendered on the course summary page. Return NULL to
     * render nothing for this plugin.
     *
     * The template will automatically get a standard layout, which
     * can be configured via attributes set on the template:
     *
     *  title        title to display, defaults to plugin name
     *  icon_url     icon for this plugin (if any)
     *  admin_url    admin link for this plugin (if any)
     *  admin_title  title for admin link (default: Administration)
     *
     * @return object   template object to render or NULL
     */
    function getInfoTemplate($course_id)
    {
        return NULL;
    }
}
