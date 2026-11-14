import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:material_ui/material_ui.dart';
import 'package:flutter/services.dart';

import '../../../core/api/api_exception.dart';
import '../../../core/widgets/form_widgets.dart';
import '../../auth/application/session_controller.dart';
import '../../auth/data/auth_repository.dart';
import '../../auth/data/models.dart';
import '../../catalog/presentation/grade_picker.dart';

class AddChildScreen extends ConsumerStatefulWidget {
  const AddChildScreen({super.key});

  @override
  ConsumerState<AddChildScreen> createState() => _AddChildScreenState();
}

class _AddChildScreenState extends ConsumerState<AddChildScreen> with ApiFormState {
  final _firstName = TextEditingController();
  final _lastName = TextEditingController();
  final _pin = TextEditingController();
  DateTime? _birthDate;
  GradeRef? _grade;
  TrackRef? _track;
  bool _consent = false;

  @override
  void dispose() {
    for (final c in [_firstName, _lastName, _pin]) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _pickBirthDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: _birthDate ?? DateTime(now.year - 8),
      firstDate: DateTime(now.year - 25),
      lastDate: DateTime(now.year - 5, now.month, now.day),
      helpText: 'Date de naissance',
    );
    if (picked != null) setState(() => _birthDate = picked);
  }

  Future<void> _save() async {
    final repo = ref.read(authRepositoryProvider);
    final child = await repo.addChild(
      firstName: _firstName.text,
      lastName: _lastName.text,
      birthDate: _birthDate!,
      pin: _pin.text,
      parentalConsent: _consent,
      gradeId: _grade?.id,
      trackId: _track?.id,
    );
    await ref.read(sessionProvider.notifier).refresh();
    if (!mounted) return;
    await showDialog<void>(
      context: context,
      builder: (_) => AlertDialog(
        title: Text('${child.firstName} est inscrit(e) !'),
        content: Text(
          'Code de connexion : ${child.loginCode}\n\nNotez ce code : votre enfant l\'utilisera avec le PIN choisi pour se connecter.',
        ),
        actions: [TextButton(onPressed: () => Navigator.pop(context), child: const Text('Compris'))],
      ),
    );
    if (mounted) context.pop();
  }

  @override
  Widget build(BuildContext context) {
    final dateLabel = _birthDate == null ? 'Choisir la date' : DateFormat('dd/MM/yyyy').format(_birthDate!);
    return Scaffold(
      appBar: AppBar(title: const Text('Ajouter un enfant')),
      body: Form(
        key: formKey,
        child: ListView(
          padding: const EdgeInsets.all(24),
          children: [
            if (error != null) ...[ErrorBanner(error!.message), const SizedBox(height: 16)],
            TextFormField(
              controller: _firstName,
              decoration: const InputDecoration(labelText: 'Prénom'),
              textCapitalization: TextCapitalization.words,
              validator: (v) => Validators.required(v, 'Le prénom') ?? serverError('first_name'),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _lastName,
              decoration: const InputDecoration(labelText: 'Nom'),
              textCapitalization: TextCapitalization.words,
              validator: (v) => Validators.required(v, 'Le nom') ?? serverError('last_name'),
            ),
            const SizedBox(height: 16),
            FormField<DateTime>(
              validator: (_) =>
                  _birthDate == null ? 'La date de naissance est obligatoire.' : serverError('birth_date'),
              builder: (field) => InputDecorator(
                decoration: InputDecoration(labelText: 'Date de naissance', errorText: field.errorText),
                child: InkWell(onTap: _pickBirthDate, child: Text(dateLabel)),
              ),
            ),
            const SizedBox(height: 16),
            GradePicker(
              grade: _grade,
              track: _track,
              gradeError: serverError('grade_id'),
              trackError: serverError('track_id'),
              onChanged: (grade, track) => setState(() {
                _grade = grade;
                _track = track;
              }),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _pin,
              decoration: const InputDecoration(
                labelText: 'PIN secret de l\'enfant (4 à 6 chiffres)',
                helperText: 'Choisissez un PIN facile à retenir pour votre enfant.',
              ),
              keyboardType: TextInputType.number,
              obscureText: true,
              maxLength: 6,
              inputFormatters: [FilteringTextInputFormatter.digitsOnly],
              validator: (v) => Validators.pin(v) ?? serverError('pin'),
            ),
            CheckboxListTile(
              value: _consent,
              onChanged: (v) => setState(() => _consent = v ?? false),
              contentPadding: EdgeInsets.zero,
              controlAffinity: ListTileControlAffinity.leading,
              title: const Text(
                'Je suis le parent ou le tuteur légal de cet enfant et je consens à la création de son compte et au traitement de ses données scolaires.',
              ),
            ),
            const SizedBox(height: 16),
            PrimaryButton(
              label: 'Créer le compte de mon enfant',
              loading: loading,
              onPressed: () => submit(() async {
                if (!_consent) {
                  throw ApiException('Le consentement parental est nécessaire pour créer le compte.');
                }
                await _save();
              }),
            ),
          ],
        ),
      ),
    );
  }
}
