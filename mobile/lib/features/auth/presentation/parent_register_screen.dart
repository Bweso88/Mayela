import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:material_ui/material_ui.dart';

import '../../../core/widgets/form_widgets.dart';
import '../application/session_controller.dart';

class ParentRegisterScreen extends ConsumerStatefulWidget {
  const ParentRegisterScreen({super.key});

  @override
  ConsumerState<ParentRegisterScreen> createState() => _ParentRegisterScreenState();
}

class _ParentRegisterScreenState extends ConsumerState<ParentRegisterScreen> with ApiFormState {
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _phone = TextEditingController();
  final _password = TextEditingController();
  bool _acceptTerms = false;

  @override
  void dispose() {
    for (final c in [_name, _email, _phone, _password]) {
      c.dispose();
    }
    super.dispose();
  }

  String? _contactValidator(String? _) {
    if (_email.text.trim().isEmpty && _phone.text.trim().isEmpty) {
      return 'Renseigne un e-mail ou un numéro de téléphone.';
    }
    return null;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Compte famille')),
      body: Form(
        key: formKey,
        child: ListView(
          padding: const EdgeInsets.all(24),
          children: [
            Text(
              'Créez votre compte parent, puis ajoutez vos enfants. Chaque enfant reçoit un code et un PIN pour se connecter.',
              style: Theme.of(context).textTheme.bodyMedium,
            ),
            const SizedBox(height: 20),
            if (error != null) ...[ErrorBanner(error!.message), const SizedBox(height: 16)],
            TextFormField(
              controller: _name,
              decoration: const InputDecoration(labelText: 'Nom complet'),
              textCapitalization: TextCapitalization.words,
              validator: (v) => Validators.required(v, 'Le nom') ?? serverError('name'),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _email,
              decoration: const InputDecoration(labelText: 'E-mail'),
              keyboardType: TextInputType.emailAddress,
              validator: (v) => _contactValidator(v) ?? serverError('email'),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _phone,
              decoration: const InputDecoration(labelText: 'Téléphone (ex. +242 06 123 45 67)'),
              keyboardType: TextInputType.phone,
              validator: (v) => serverError('phone'),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _password,
              decoration: const InputDecoration(labelText: 'Mot de passe'),
              obscureText: true,
              validator: (v) => Validators.password(v) ?? serverError('password'),
            ),
            const SizedBox(height: 8),
            CheckboxListTile(
              value: _acceptTerms,
              onChanged: (v) => setState(() => _acceptTerms = v ?? false),
              contentPadding: EdgeInsets.zero,
              controlAffinity: ListTileControlAffinity.leading,
              title: const Text('J\'accepte les conditions d\'utilisation et la politique de confidentialité.'),
              subtitle: serverError('accept_terms') != null
                  ? Text(serverError('accept_terms')!, style: TextStyle(color: Theme.of(context).colorScheme.error))
                  : null,
            ),
            const SizedBox(height: 16),
            PrimaryButton(
              label: 'Créer mon compte',
              loading: loading,
              onPressed: () => submit(
                () => ref
                    .read(sessionProvider.notifier)
                    .registerParent(
                      name: _name.text,
                      email: _email.text,
                      phone: _phone.text.replaceAll(' ', ''),
                      password: _password.text,
                      acceptTerms: _acceptTerms,
                    ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
