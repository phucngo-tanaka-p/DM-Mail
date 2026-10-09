<?php

use App\Models\Recipient;
use App\Rules\EmailList;
use App\Support\AddressList;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('送信リスト')] class extends Component
{
    use WithPagination;

    public const PER_PAGE = 50;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public bool $showFormModal = false;

    #[Locked]
    public ?int $editingId = null;

    public string $email = '';

    public string $cc = '';

    public string $bcc = '';

    public string $company_name = '';

    public string $person_name = '';

    public string $honorific = '様';

    /** @var list<string> 追加項目の列名（フォームを開いた時点） */
    #[Locked]
    public array $custom_names = [];

    /** @var list<string> custom_names と同じ順の値 */
    public array $custom_values = [];

    public bool $exclude = false;

    public bool $showDeleteModal = false;

    #[Locked]
    public ?int $deletingId = null;

    public bool $showResubscribeModal = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Recipient>
     */
    #[Computed]
    public function recipients(): LengthAwarePaginator
    {
        return Recipient::query()
            ->search($this->search)
            ->orderBy('id')
            ->paginate(self::PER_PAGE);
    }

    /**
     * @return list<string>
     */
    #[Computed]
    public function customFieldNames(): array
    {
        return Recipient::customFieldNames();
    }

    #[Computed]
    public function totalCount(): int
    {
        return Recipient::query()->count();
    }

    #[Computed]
    public function editingRecipient(): ?Recipient
    {
        return $this->editingId ? Recipient::find($this->editingId) : null;
    }

    public function create(): void
    {
        $this->resetForm();
        $this->custom_names = $this->customFieldNames;
        $this->custom_values = array_fill(0, count($this->custom_names), '');
        $this->showFormModal = true;
    }

    public function edit(int $id): void
    {
        $recipient = Recipient::findOrFail($id);

        $this->resetForm();
        $this->editingId = $recipient->id;
        $this->email = $recipient->email;
        $this->cc = $recipient->cc ?? '';
        $this->bcc = $recipient->bcc ?? '';
        $this->company_name = $recipient->company_name ?? '';
        $this->person_name = $recipient->person_name ?? '';
        $this->honorific = $recipient->honorific;
        $this->exclude = $recipient->exclude;
        $this->custom_names = $this->customFieldNames;
        $this->custom_values = array_map(
            fn (string $name): string => (string) ($recipient->custom_fields[$name] ?? ''),
            $this->custom_names,
        );
        $this->showFormModal = true;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'cc' => ['nullable', 'string', 'max:2000', new EmailList],
            'bcc' => ['nullable', 'string', 'max:2000', new EmailList],
            'company_name' => ['nullable', 'string', 'max:255'],
            'person_name' => ['nullable', 'string', 'max:255'],
            'honorific' => ['nullable', 'string', 'max:20'],
            'custom_values' => ['array'],
            'custom_values.*' => ['nullable', 'string', 'max:1000'],
            'exclude' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        $attributes = [
            'email' => 'メールアドレス',
            'cc' => 'CC',
            'bcc' => 'BCC',
            'company_name' => '会社名',
            'person_name' => '名前',
            'honorific' => '敬称',
        ];

        foreach ($this->custom_names as $index => $name) {
            $attributes["custom_values.{$index}"] = $name;
        }

        return $attributes;
    }

    public function save(): void
    {
        foreach (['email', 'cc', 'bcc', 'company_name', 'person_name', 'honorific'] as $field) {
            $this->{$field} = Str::trim($this->{$field});
        }
        $this->custom_values = array_map(fn (?string $value): string => Str::trim($value ?? ''), $this->custom_values);

        $validated = $this->validate();

        $customFields = [];
        foreach ($this->custom_names as $index => $name) {
            if (($validated['custom_values'][$index] ?? '') !== '') {
                $customFields[$name] = $validated['custom_values'][$index];
            }
        }

        $attributes = [
            'email' => $validated['email'],
            'cc' => AddressList::normalize($validated['cc']),
            'bcc' => AddressList::normalize($validated['bcc']),
            'company_name' => $validated['company_name'] ?: null,
            'person_name' => $validated['person_name'] ?: null,
            'honorific' => $validated['honorific'] ?? '',
            'custom_fields' => $customFields ?: null,
            'exclude' => $validated['exclude'],
        ];

        if ($this->editingId) {
            Recipient::findOrFail($this->editingId)->update($attributes);
            Flux::toast(variant: 'success', text: '保存しました。');
        } else {
            Recipient::create($attributes);
            Flux::toast(variant: 'success', text: '追加しました。');
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function toggleExclude(int $id): void
    {
        $recipient = Recipient::findOrFail($id);

        if ($recipient->isUnsubscribed()) {
            return;
        }

        $recipient->update(['exclude' => ! $recipient->exclude]);
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = Recipient::findOrFail($id)->id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            // モデル経由で削除し、追加項目の列名キャッシュも更新させる
            Recipient::find($this->deletingId)?->delete();
            Flux::toast(variant: 'success', text: '削除しました。');
        }

        $this->showDeleteModal = false;
        $this->deletingId = null;

        unset($this->recipients, $this->totalCount);

        // 最後のページの最後の1件を消したら、空のページではなく残っている最後のページへ戻す
        if ($this->recipients->isEmpty() && $this->getPage() > 1) {
            $this->setPage($this->recipients->lastPage());
        }
    }

    public function resubscribe(): void
    {
        $recipient = $this->editingRecipient;

        if ($recipient?->isUnsubscribed()) {
            $recipient->forceFill(['unsubscribed_at' => null])->save();
            unset($this->editingRecipient);
            Flux::toast(variant: 'success', text: '配信停止を解除しました。');
        }

        $this->showResubscribeModal = false;
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'email', 'cc', 'bcc', 'company_name', 'person_name',
            'honorific', 'custom_names', 'custom_values', 'exclude',
        ]);
        $this->resetValidation();
        unset($this->editingRecipient);
    }
};
?>

<section class="w-full">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">送信リスト</flux:heading>
            <flux:text class="mt-2">全 {{ number_format($this->totalCount) }} 件</flux:text>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="create" data-test="add-recipient">追加する</flux:button>
    </div>

    <div class="mt-6 max-w-md">
        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            placeholder="No・メールアドレス・会社名・名前などで検索"
            clearable
            aria-label="検索"
        />
    </div>

    @if ($this->recipients->isEmpty())
        <flux:text class="mt-10 text-center">
            {{ $this->totalCount === 0 ? 'まだ宛先がありません。「追加する」から登録してください。' : '該当する宛先がありません。' }}
        </flux:text>
    @else
        <flux:table :paginate="$this->recipients" class="mt-6">
            <flux:table.columns sticky class="bg-white dark:bg-zinc-800">
                <flux:table.column align="end">No</flux:table.column>
                <flux:table.column>メールアドレス</flux:table.column>
                <flux:table.column>会社名</flux:table.column>
                <flux:table.column>名前</flux:table.column>
                <flux:table.column>敬称</flux:table.column>
                @foreach ($this->customFieldNames as $name)
                    <flux:table.column wire:key="column-{{ $loop->index }}">{{ $name }}</flux:table.column>
                @endforeach
                <flux:table.column align="center">送信しない</flux:table.column>
                <flux:table.column>最終送信日</flux:table.column>
                <flux:table.column><span class="sr-only">操作</span></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->recipients as $recipient)
                    <flux:table.row :key="$recipient->id" @class(['opacity-60' => $recipient->isUnsubscribed()])>
                        <flux:table.cell align="end" class="tabular-nums">{{ $recipient->id }}</flux:table.cell>
                        <flux:table.cell variant="strong">
                            {{ $recipient->email }}
                            @if ($recipient->cc || $recipient->bcc)
                                <div class="text-xs font-normal text-zinc-500">
                                    @if ($recipient->cc) CC：{{ str_replace(';', '; ', $recipient->cc) }} @endif
                                    @if ($recipient->bcc) BCC：{{ str_replace(';', '; ', $recipient->bcc) }} @endif
                                </div>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $recipient->company_name }}</flux:table.cell>
                        <flux:table.cell>{{ $recipient->person_name }}</flux:table.cell>
                        <flux:table.cell>{{ $recipient->honorific }}</flux:table.cell>
                        @foreach ($this->customFieldNames as $name)
                            <flux:table.cell wire:key="cell-{{ $recipient->id }}-{{ $loop->index }}">{{ $recipient->custom_fields[$name] ?? '' }}</flux:table.cell>
                        @endforeach
                        <flux:table.cell align="center">
                            @if ($recipient->isUnsubscribed())
                                <flux:badge size="sm" color="zinc">配信停止</flux:badge>
                            @else
                                <flux:checkbox
                                    :checked="$recipient->exclude"
                                    wire:click="toggleExclude({{ $recipient->id }})"
                                    aria-label="No.{{ $recipient->id }} を送信しない"
                                />
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap tabular-nums">
                            {{ $recipient->last_sent_at?->format('Y/m/d H:i') ?? '—' }}
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $recipient->id }})">編集</flux:button>
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="confirmDelete({{ $recipient->id }})" aria-label="No.{{ $recipient->id }} を削除" />
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    {{-- 追加・編集 --}}
    <flux:modal wire:model.self="showFormModal" flyout class="md:w-xl">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ $editingId ? "No.{$editingId} を編集" : '宛先を追加' }}</flux:heading>

            @if ($this->editingRecipient?->isUnsubscribed())
                <flux:callout variant="secondary" icon="no-symbol">
                    <flux:callout.heading>配信停止中（{{ $this->editingRecipient->unsubscribed_at->format('Y/m/d') }}）</flux:callout.heading>
                    <flux:callout.text>本人の希望により、この宛先にはメールを送信しません。</flux:callout.text>
                    <x-slot name="actions">
                        <flux:button size="sm" wire:click="$set('showResubscribeModal', true)">配信停止を解除する</flux:button>
                    </x-slot>
                </flux:callout>
            @endif

            <flux:input wire:model="email" type="email" label="メールアドレス" required />
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="company_name" label="会社名" />
                <flux:input wire:model="person_name" label="名前" />
            </div>
            <flux:input wire:model="honorific" label="敬称" description="例：様、御中" class="max-w-32" />
            <flux:input wire:model="cc" label="CC" badge="任意" description="複数の場合は「;」で区切ってください。" />
            <flux:input wire:model="bcc" label="BCC" badge="任意" description="複数の場合は「;」で区切ってください。" />

            @if ($custom_names !== [])
                <flux:fieldset>
                    <flux:legend>追加項目</flux:legend>
                    <div class="space-y-4">
                        @foreach ($custom_names as $index => $name)
                            <flux:input wire:key="custom-{{ $index }}" wire:model="custom_values.{{ $index }}" :label="$name" />
                        @endforeach
                    </div>
                </flux:fieldset>
            @endif

            <flux:checkbox wire:model="exclude" label="送信しない" description="チェックすると、一括送信の対象から外れます。" />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">キャンセル</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" data-test="save-recipient">{{ $editingId ? '保存する' : '追加する' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- 削除の確認 --}}
    <flux:modal wire:model.self="showDeleteModal" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">No.{{ $deletingId }} を削除しますか？</flux:heading>
                <flux:text class="mt-2">送信リストから削除します。この操作は取り消せません。<br>（これまでの送信履歴は残ります）</flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">キャンセル</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="delete" data-test="confirm-delete">削除する</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- 配信停止の解除（警告） --}}
    <flux:modal wire:model.self="showResubscribeModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">配信停止を解除しますか？</flux:heading>
                <flux:text class="mt-2">
                    この宛先は本人の希望で配信を停止しています。本人の同意なく送信を再開すると、特定電子メール法に違反するおそれがあります。<br>
                    <strong>本人から再開の申し出があった場合のみ</strong>解除してください。
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">キャンセル</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="resubscribe" data-test="confirm-resubscribe">解除する</flux:button>
            </div>
        </div>
    </flux:modal>
</section>
