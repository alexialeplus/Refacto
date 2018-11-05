<?php

class TemplateManager
{
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

    private function computeText($text, array $data)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;
        if ($quote)
        {
            /* KEEP IN CASE OF OTHER USE THAN QUOTE ID*/
            /*$_quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);*/
            $site = SiteRepository::getInstance()->getById($quote->siteId);
            $destination = DestinationRepository::getInstance()->getById($quote->destinationId);

            $text = $this->replaceText($text, '[quote:summary_html]', Quote::renderHtml($quote));
            $text = $this->replaceText($text, '[quote:summary]', Quote::renderText($quote));

            if ($destination)
                $text = $this->replaceText($text, '[quote:destination_name]', $destination->countryName);

            if ($destination && $site)
            {
                $link = $site->url . '/' . $destination->countryName . '/quote/' . $quote->id;
                $text = $this->replaceText($text, '[quote:destination_link]', $link);
            }
        }

        /*
         * USER
         * [user:*]
         */
        $_user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $APPLICATION_CONTEXT->getCurrentUser();
        if($_user)
            $text = $this->replaceText($text, '[user:first_name]', ucfirst(mb_strtolower($_user->firstname)));

        return $text;
    }

    private function replaceText($text, $needle, $replace)
    {
        if (strpos($text, $needle) !== false)
        {
            $text = str_replace($needle, $replace, $text);
        }

        return $text;
    }
}
