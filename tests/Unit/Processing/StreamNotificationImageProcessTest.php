<?php

namespace Tests\Unit\Processing;

use App\Processing\StreamNotificationImageProcess;
use Tests\TestCase;

class StreamNotificationImageProcessTest extends TestCase
{
    public function test_it_renders_a_jpeg_and_changes_when_program_or_host_changes(): void
    {
        $process = new StreamNotificationImageProcess;
        $image = $process->render('Conexão Akiba', 'Yagami');
        $size = getimagesizefromstring($image);
        $this->assertSame([1536, 1024], [$size[0], $size[1]]);
        $this->assertSame('image/jpeg', $size['mime']);
        $this->assertNotSame($image, $process->render('Noite Otaku', 'Yagami'));
        $this->assertNotSame($image, $process->render('Conexão Akiba', 'João'));
    }

    public function test_long_accented_titles_do_not_overwrite_the_presenter(): void
    {
        $process = new StreamNotificationImageProcess;
        $short = imagecreatefromstring($process->render('Akiba', 'DJ'));
        $long = imagecreatefromstring($process->render(str_repeat('ProgramaçãoÁ', 30), str_repeat('João ', 50)));
        // JPEG blocks on the right must remain identical regardless of the text length.
        for ($x = 900; $x < 1500; $x += 40) {
            for ($y = 250; $y < 750; $y += 40) {
                $this->assertSame(imagecolorat($short, $x, $y), imagecolorat($long, $x, $y));
            }
        }
        imagedestroy($short);
        imagedestroy($long);
    }
}
