<?php

namespace App\Integrations\MetaSocial;

enum PublicationPlacement: string
{
    case Feed = 'feed';
    case Story = 'story';
}
