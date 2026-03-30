<?php

declare(strict_types=1);

namespace Kentec\Kernel\Database\EntityInterfaces;

/**
 * Interface marqueur pour les modèles dont la table hérite de historytable.
 * Les modèles qui implémentent cette interface auront automatiquement
 * le filtre "isactive = true" appliqué sur les requêtes SELECT.
 */
interface HistoryInterface
{
    public function getCreatedat(): ?string;

    public function getUpdatedat(): ?string;

    public function getCreatedby(): ?string;

    public function getUpdatedby(): ?string;

    public function getIsactive(): bool;
}
