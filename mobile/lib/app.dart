import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:material_ui/material_ui.dart';

import 'core/config/env.dart';
import 'core/router/router.dart';
import 'core/theme/mayela_theme.dart';
import 'core/theme/universe.dart';

class MayelaApp extends ConsumerWidget {
  const MayelaApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return MaterialApp.router(
      title: Env.appName,
      debugShowCheckedModeBanner: false,
      theme: MayelaTheme.of(Universe.junior),
      routerConfig: ref.watch(routerProvider),
    );
  }
}
