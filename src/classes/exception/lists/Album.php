<?php
declare(strict_types = 1);
namespace iutnc\deefy\audio\lists;

class Album extends AudioList{

    private string $artiste;
    private string $dateSortie;

    public function __construct(string $nom, array $tab = [], string $artiste, string $dateSortie) {
        parent::__construct($nom, $tab);
        $this->artiste = $artiste;
        $this->dateSortie = $dateSortie;
    }

    public function __get(string $att): mixed {
        if (property_exists($this, $att)) {
            return $this->$att;
        }
        return parent::__get($att);
    }

    public function setArtiste(string $artiste): void {
        $this->artiste = $artiste;
    }

    public function setDateSortie(string $dateSortie): void {
        $this->dateSortie = $dateSortie;
    }

}