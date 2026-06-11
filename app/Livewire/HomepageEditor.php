<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Setting;
use App\Models\UserHomepageLayout;
use Illuminate\Support\Facades\Auth;

class HomepageEditor extends Component {

    public bool $editing = false;
    public array $modules = [];
    public array $availableModules = [];
    public bool $showAddPanel = false;

    public static function moduleTypes(): array {
        return [
            'collection' => ['icon' => 'fa-layer-group', 'label' => 'Подборка новелл', 'color' => 'emerald'],
            'updates'    => ['icon' => 'fa-bolt', 'label' => 'Обновления', 'color' => 'indigo'],
            'continue'   => ['icon' => 'fa-book-open', 'label' => 'Продолжить чтение', 'color' => 'violet'],
            'favorites'  => ['icon' => 'fa-heart', 'label' => 'Обновления избранного', 'color' => 'pink'],
            'top_rated'  => ['icon' => 'fa-star', 'label' => 'Топ по рейтингу', 'color' => 'amber'],
            'random'     => ['icon' => 'fa-shuffle', 'label' => 'Случайная подборка', 'color' => 'sky'],
            'schedule'   => ['icon' => 'fa-calendar', 'label' => 'Расписание выхода', 'color' => 'teal'],
            'banner_custom' => ['icon' => 'fa-image', 'label' => 'Баннер', 'color' => 'slate'],
        ];
    }

    public static function layoutVariants(): array {
        return [
            'swiper' => 'Свайпер',
            'grid'   => 'Сетка',
            'list'   => 'Список',
        ];
    }

    public function mount() {
        $this->loadLayout();
    }

    private function loadLayout() {
        $adminLayout = json_decode(Setting::retrieve('homepage_layout', '[]'), true) ?: [];

        $defaults = [];
        foreach ($adminLayout as $block) {
            $defaults[] = $this->normalizeBlock($block);
        }

        if (Auth::check()) {
            $saved = UserHomepageLayout::where('user_id', Auth::id())->first();
            if ($saved && !empty($saved->layout)) {
                $this->modules = $saved->layout;
            } else {
                $this->modules = $defaults;
            }
        } else {
            $this->modules = $defaults;
        }

        $this->availableModules = $this->buildAvailableModules($adminLayout);
    }

    private function normalizeBlock(array $block): array {
        $type = $block['type'] ?? 'collection';
        if ($type === 'block') $type = 'collection';

        return [
            'id'       => $block['id'] ?? uniqid('mod_'),
            'type'     => $type,
            'title'    => $block['title'] ?? self::moduleTypes()[$type]['label'] ?? 'Модуль',
            'visible'  => $block['visible'] ?? true,
            'layout'   => $block['layout'] ?? 'swiper',
            'sort'     => $block['sort'] ?? 'views',
            'period'   => $block['period'] ?? 'month',
            'rows'     => $block['rows'] ?? 1,
            'pinned'   => $block['pinned'] ?? false,
            'custom_content' => $block['custom_content'] ?? null,
        ];
    }

    private function buildAvailableModules(array $adminBlocks): array {
        $available = [];

        foreach ($adminBlocks as $block) {
            $type = ($block['type'] ?? 'block') === 'block' ? 'collection' : ($block['type'] ?? 'collection');
            $available[] = [
                'type'  => $type,
                'title' => $block['title'] ?? self::moduleTypes()[$type]['label'] ?? 'Модуль',
                'sort'  => $block['sort'] ?? 'views',
                'period' => $block['period'] ?? 'month',
                'layout' => $block['layout'] ?? 'grid',
                'rows'  => $block['rows'] ?? 1,
                'custom_content' => $block['custom_content'] ?? null,
                'source' => 'admin',
            ];
        }

        if (Auth::check()) {
            $available[] = ['type' => 'continue', 'title' => 'Продолжить чтение', 'source' => 'system'];
            $available[] = ['type' => 'favorites', 'title' => 'Обновления избранного', 'source' => 'system'];
        }
        $available[] = ['type' => 'top_rated', 'title' => 'Топ по рейтингу', 'source' => 'system'];
        $available[] = ['type' => 'random', 'title' => 'Случайная подборка', 'source' => 'system'];
        $available[] = ['type' => 'schedule', 'title' => 'Расписание выхода', 'source' => 'system'];

        return $available;
    }

    public function toggleEdit() {
        $this->editing = !$this->editing;
        $this->showAddPanel = false;
    }

    public function moveUp(int $index) {
        if ($index <= 0 || $index >= count($this->modules)) return;
        [$this->modules[$index], $this->modules[$index - 1]] = [$this->modules[$index - 1], $this->modules[$index]];
        $this->modules = array_values($this->modules);
    }

    public function moveDown(int $index) {
        if ($index < 0 || $index >= count($this->modules) - 1) return;
        [$this->modules[$index], $this->modules[$index + 1]] = [$this->modules[$index + 1], $this->modules[$index]];
        $this->modules = array_values($this->modules);
    }

    public function toggleVisibility(int $index) {
        if (!isset($this->modules[$index])) return;
        if ($this->modules[$index]['pinned'] ?? false) return;
        $this->modules[$index]['visible'] = !($this->modules[$index]['visible'] ?? true);
    }

    public function changeLayout(int $index, string $layout) {
        if (!isset($this->modules[$index])) return;
        $this->modules[$index]['layout'] = $layout;
    }

    public function removeModule(int $index) {
        if (!isset($this->modules[$index])) return;
        if ($this->modules[$index]['pinned'] ?? false) return;
        array_splice($this->modules, $index, 1);
        $this->modules = array_values($this->modules);
    }

    public function addModule(string $type, string $title = '') {
        $meta = self::moduleTypes()[$type] ?? null;
        if (!$meta) return;

        $this->modules[] = [
            'id'      => uniqid('mod_'),
            'type'    => $type,
            'title'   => $title ?: $meta['label'],
            'visible' => true,
            'layout'  => 'swiper',
            'sort'    => 'views',
            'period'  => 'month',
            'rows'    => 1,
            'pinned'  => false,
        ];
        $this->showAddPanel = false;
    }

    public function saveLayout() {
        if (!Auth::check()) return;

        UserHomepageLayout::updateOrCreate(
            ['user_id' => Auth::id()],
            ['layout' => $this->modules]
        );
        $this->editing = false;
        $this->dispatch('layout-saved');
    }

    public function resetToDefault() {
        if (!Auth::check()) return;
        UserHomepageLayout::where('user_id', Auth::id())->delete();
        $this->loadLayout();
        $this->editing = false;
    }

    public function render() {
        return view('livewire.homepage-editor');
    }
}
