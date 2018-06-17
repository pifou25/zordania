<?php

/*
 * Paginator to paginate a query
 */
class Paginator {

    private $query;
    private $count;
    
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
    public function __construct(\Illuminate\Database\Eloquent\Builder $query, int $limit = LIMIT_PAGE) {
        $this->query = $query;
        $this->count = $query->count();
        // _GET[page] -1
        $this->page = request('page', 'uint', 'get', 1) - 1;

        $this->get = $this->query->skip($this->page * $limit)->take($limit)->get()->toArray();
        $this->links = $this->list_page($this->page, (int) $this->count / $limit);
    }

    /**
     * 
     * @param int $page = page en cours
     * @param int $nb_page = nb total de pages
     * @param int $nb le nombre de pages à retourner à droite et à gauche
     * @return array
     */
    private function list_page(int $page, int $nb_page, int $nb = 3) {
        for ($i = 0; $i <= $nb_page; $i++) {
            if (($i <= $nb) OR ( $i >= $nb_page - $nb) OR ( ($i < $page + $nb) AND ( $i > $page - $nb)))
                $list_page[] = $i + 1;
            else {
                if ($i >= $nb AND $i <= $page - $nb)
                    $i = $page - $nb;
                elseif ($i >= $page + $nb AND $i <= $nb_page - $nb)
                    $i = $nb_page - $nb;
                $list_page[] = '...';
            }
        }
        return $list_page;
    }

}
