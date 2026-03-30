import sys

file_path = r"c:\Users\humeyra.cimen\Desktop\yunusEmre\laraveldynamicqr\resources\views\qr\form.blade.php"

with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

new_html = r"""                        <div class="qr-form-stack">
                            @if(auth()->user()->hasGlobalAccess())
                                <div class="col-span-full mb-1">
                                    <label for="department_id" class="qr-form-field-label">Birim (Admin)</label>
                                    <select id="department_id" name="department_id" required class="field-shell qr-form-input appearance-none bg-no-repeat bg-[right_1.25rem_center] bg-[length:1.2em_1.2em]" style="background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22currentColor%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22%3E%3Cpolyline points=%226 9 12 15 18 9%22/%3E%3C/svg%3E');">
                                        <option value="">Birim Seciniz</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" @selected(old('department_id', $qrCode->department_id) == $dept->id)>
                                                {{ $dept->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <span class="mt-1 block text-[0.78rem] font-semibold text-rose-500">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif

                            <div>
                                <label for="title" class="qr-form-field-label">Baslik</label>"""

content = content.replace("""                        <div class="qr-form-stack">
                            <div>
                                <label for="title" class="qr-form-field-label">Baslik</label>""", new_html)

with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)
print("Form updated")
