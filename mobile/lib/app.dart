import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:material_ui/material_ui.dart';

import 'core/config/env.dart';
import 'core/router/router.dart';
import 'core/theme/mayela_theme.dart';
import 'core/theme/universe.dart';
import 'features/auth/application/session_controller.dart';

/// Thème de l'app : univers de la classe de l'élève connecté, Junior sinon (accueil, parents).
Universe _universe(WidgetRef ref) {
  final profile = ref.watch(sessionProvider).value?.studentProfile;
  return profile?.universeAt(DateTime.now()) ?? Universe.junior;
}

class MayelaApp extends ConsumerWidget {
  const MayelaApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return MaterialApp.router(
      title: Env.appName,
      debugShowCheckedModeBanner: false,
      theme: MayelaTheme.of(_universe(ref)),
      routerConfig: ref.watch(routerProvider),
    );
  }
}
