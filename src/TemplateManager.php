<?php

class TemplateManager
{
    /**
     * @param Template $tpl
     * @param array $data
     *
     * @return Template
     */
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    /**
     * @param string $text
     * @param array $data
     *
     * @return string Final text with replaced values
     */
    private function computeText($text, array $data)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();
        $textToReplace = array();

        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;
        if ($quote)
        {
            /* KEEP IN CASE OF OTHER USE THAN QUOTE ID*/
            /*$_quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);*/
            $site = SiteRepository::getInstance()->getById($quote->siteId);
            $destination = DestinationRepository::getInstance()->getById($quote->destinationId);

            /* Put all placeholders to replace here like this : '[placeholder]' => 'your text'*/
            $textToReplace = array(
                '[quote:summary_html]' => Quote::renderHtml($quote),
                '[quote:summary]' => Quote::renderText($quote),
            );

            if ($destination)
                $textToReplace['[quote:destination_name]'] = $destination->countryName;

            if ($destination && $site)
            {
                $link = $site->url . '/' . $destination->countryName . '/quote/' . $quote->id;
                $textToReplace['[quote:destination_link]'] = $link;
            }
        }

        /*
         * USER
         * [user:*]
         */
        $_user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $APPLICATION_CONTEXT->getCurrentUser();
        if($_user)
            $textToReplace['[user:first_name]'] = ucfirst(mb_strtolower($_user->firstname));


        foreach ($textToReplace as $needle => $replace)
        {
            $text = $this->replaceText($text, $needle, $replace);
        }

        return $text;
    }

    /**
     * Look for a placeholder and replace it
     *
     * @param string $text
     * @param string $needle
     * @param string $replace
     *
     * @return string Final text with replaced values
     */
    private function replaceText($text, $needle, $replace)
    {
        if (strpos($text, $needle) !== false)
        {
            $text = str_replace($needle, $replace, $text);
        }

        return $text;
    }
}
