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
    #[Validate]
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

    protected function rules()
    {
        return [
            'content' => [
                'required',
                'min:5',
                function ($attribute, $value, $fail) {
                    // Check if content is empty or just empty lists
                    $textContent = strip_tags($value);
                    $textContent = trim($textContent);

                    if (empty($textContent)) {
                        $fail('The content field is required and cannot be empty');
                    }

                    if (strlen($textContent) < 5) {
                        $fail('The minimum character of content is 5 characters.');
                    }
                }
            ]
        ];
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
