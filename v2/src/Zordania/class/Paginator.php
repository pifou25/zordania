<?php

/*
 * Paginator to paginate a query
 */

class Paginator {

    private $query;
    private $count;

    const LIMIT = LIMIT_PAGE;

    // public property to access via the template
    public $get; // the result
    public $link; // all displayed pages

    /**
     * Create a new Paginator instance
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  \Illuminate\Database\Query\Grammars\Grammar  $grammar
     * @param  \Illuminate\Database\Query\Processors\Processor  $processor
     * @return void
     */

    public function __construct(\Illuminate\Database\Eloquent\Builder $query, int $skip = 0) {
        $this->query = $query;
        $this->count = $query->count();
        if ($skip == 0) {
            // _GET[page] -1
            $this->page = request('page', 'uint', 'get', 1) - 1;
        } else {
            $this->page = ceil($skip / self::LIMIT) - 1;
        }
        $this->get = $this->query->skip($this->page * self::LIMIT)->take(self::LIMIT)->get()->toArray();
        $this->links = self::listPages($this->page, (int) ($this->count - 1) / self::LIMIT);
    }

    /**
     * 
     * @param int $page = page en cours
     * @param int $nb_page = nb total de pages
     * @param int $nb le nombre de pages à retourner à droite et à gauche
     * @return array
     */
    public static function listPages(int $page, int $nb_page, int $nb = 3) {
        for ($i = 0; $i <= $nb_page; $i++) {
            if (($i <= $nb) OR ( $i >= $nb_page - $nb) OR ( ($i < $page + $nb) AND ( $i > $page - $nb)))
                $listPages[] = $i + 1;
            else {
                if ($i >= $nb AND $i <= $page - $nb)
                    $i = $page - $nb;
                elseif ($i >= $page + $nb AND $i <= $nb_page - $nb)
                    $i = $nb_page - $nb;
                $listPages[] = '...';
            }
        }
        return $listPages;
    }

}
