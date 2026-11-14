import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:material_ui/material_ui.dart';

import '../../../core/widgets/form_widgets.dart';
import '../application/session_controller.dart';

/// Connexion parents et élèves autonomes (≥ 15 ans).
class PasswordLoginScreen extends ConsumerStatefulWidget {
  const PasswordLoginScreen({super.key});

  @override
  ConsumerState<PasswordLoginScreen> createState() => _PasswordLoginScreenState();
}

class _PasswordLoginScreenState extends ConsumerState<PasswordLoginScreen> with ApiFormState {
  final _identifier = TextEditingController();
  final _password = TextEditingController();

  @override
  void dispose() {
    _identifier.dispose();
    _password.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Connexion')),
      body: Form(
        key: formKey,
        child: ListView(
          padding: const EdgeInsets.all(24),
          children: [
            if (error != null) ...[ErrorBanner(error!.message), const SizedBox(height: 16)],
            TextFormField(
              controller: _identifier,
              decoration: const InputDecoration(labelText: 'E-mail ou téléphone'),
              keyboardType: TextInputType.emailAddress,
              autofillHints: const [AutofillHints.username],
              validator: (v) => Validators.required(v, 'L\'identifiant') ?? serverError('identifier'),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _password,
              decoration: const InputDecoration(labelText: 'Mot de passe'),
              obscureText: true,
              autofillHints: const [AutofillHints.password],
              validator: (v) => Validators.required(v, 'Le mot de passe'),
            ),
            const SizedBox(height: 24),
            PrimaryButton(
              label: 'Se connecter',
              loading: loading,
              onPressed: () =>
                  submit(() => ref.read(sessionProvider.notifier).loginWithPassword(_identifier.text, _password.text)),
            ),
          ],
        ),
      ),
    );
  }
}
