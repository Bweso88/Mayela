import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:material_ui/material_ui.dart';
import 'package:flutter/services.dart';

import '../../../core/widgets/form_widgets.dart';
import '../../../core/widgets/leo_avatar.dart';
import '../application/session_controller.dart';

/// Connexion enfant : code élève (fourni par le parent) + PIN.
class StudentLoginScreen extends ConsumerStatefulWidget {
  const StudentLoginScreen({super.key});

  @override
  ConsumerState<StudentLoginScreen> createState() => _StudentLoginScreenState();
}

class _StudentLoginScreenState extends ConsumerState<StudentLoginScreen> with ApiFormState {
  final _code = TextEditingController();
  final _pin = TextEditingController();

  @override
  void dispose() {
    _code.dispose();
    _pin.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Connexion élève')),
      body: Form(
        key: formKey,
        child: ListView(
          padding: const EdgeInsets.all(24),
          children: [
            const Center(child: LeoAvatar()),
            const SizedBox(height: 12),
            Text(
              'Coucou ! Tape le code que ton parent t\'a donné, puis ton PIN secret.',
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.bodyLarge,
            ),
            const SizedBox(height: 24),
            if (error != null) ...[ErrorBanner(error!.message), const SizedBox(height: 16)],
            TextFormField(
              key: const Key('student-code'),
              controller: _code,
              decoration: const InputDecoration(labelText: 'Mon code élève', hintText: 'MY-XXXXXX'),
              textCapitalization: TextCapitalization.characters,
              style: const TextStyle(fontSize: 22, letterSpacing: 2, fontWeight: FontWeight.w700),
              validator: (v) => Validators.required(v, 'Ton code') ?? serverError('login_code'),
            ),
            const SizedBox(height: 16),
            TextFormField(
              key: const Key('student-pin'),
              controller: _pin,
              decoration: const InputDecoration(labelText: 'Mon PIN secret'),
              keyboardType: TextInputType.number,
              obscureText: true,
              maxLength: 6,
              inputFormatters: [FilteringTextInputFormatter.digitsOnly],
              style: const TextStyle(fontSize: 22, letterSpacing: 8),
              validator: Validators.pin,
            ),
            const SizedBox(height: 16),
            PrimaryButton(
              label: 'C\'est parti !',
              loading: loading,
              onPressed: () =>
                  submit(() => ref.read(sessionProvider.notifier).loginWithStudentCode(_code.text, _pin.text)),
            ),
          ],
        ),
      ),
    );
  }
}
