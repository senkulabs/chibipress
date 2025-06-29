<?php

namespace App\Livewire\Forms;

use App\Models\Page;
use Livewire\Attributes\Validate;
use Livewire\Form;

class PageForm extends Form
{
    public ?Page $page;

    #[Validate('required|min:5')]
    public $title = '';

    #[Validate('required|min:5')]
    public $content = '';

    #[Validate('required')]
    public $status = 'draft';

    public function setPage(Page $page)
    {
        $this->page = $page;
        $this->title = $page->title;
        $this->content = $page->content;
        $this->status = $page->status;
    }

    public function store()
    {
        $this->validate();

        Page::create(
            $this->only(['title', 'content', 'status'])
        );

        $this->reset();

        $this->page = new Page();
    }

    public function update()
    {
        $this->validate();

        $this->page->update(
            $this->only(['title', 'content', 'status'])
        );
    }
}
