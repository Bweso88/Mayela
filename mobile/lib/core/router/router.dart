import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:material_ui/material_ui.dart';

import '../../features/auth/application/session_controller.dart';
import '../../features/auth/data/models.dart';
import '../../features/auth/presentation/parent_register_screen.dart';
import '../../features/auth/presentation/password_login_screen.dart';
import '../../features/auth/presentation/student_login_screen.dart';
import '../../features/auth/presentation/welcome_screen.dart';
import '../../features/parent/presentation/add_child_screen.dart';
import '../../features/parent/presentation/parent_home_screen.dart';
import '../../features/student/presentation/student_home_screen.dart';

const _publicRoutes = {'/', '/login', '/login/student', '/register/parent'};

/// Redirection pure (testable) selon l'état de session.
String? redirectFor(AsyncValue<AppUser?> session, String location) {
  if (session.isLoading && !session.hasValue) return location == '/splash' ? null : '/splash';

  final user = session.value;
  if (user == null) return _publicRoutes.contains(location) ? null : '/';

  final home = user.role == AppRole.parent ? '/parent' : '/student';
  final allowed = location.startsWith(home);
  return allowed ? null : home;
}

final routerProvider = Provider<GoRouter>((ref) {
  final refresh = ValueNotifier(0);
  ref.listen(sessionProvider, (_, _) => refresh.value++);
  ref.onDispose(refresh.dispose);

  return GoRouter(
    initialLocation: '/splash',
    refreshListenable: refresh,
    redirect: (context, state) => redirectFor(ref.read(sessionProvider), state.matchedLocation),
    routes: [
      GoRoute(path: '/splash', builder: (_, _) => const _SplashScreen()),
      GoRoute(path: '/', builder: (_, _) => const WelcomeScreen()),
      GoRoute(path: '/login', builder: (_, _) => const PasswordLoginScreen()),
      GoRoute(path: '/login/student', builder: (_, _) => const StudentLoginScreen()),
      GoRoute(path: '/register/parent', builder: (_, _) => const ParentRegisterScreen()),
      GoRoute(
        path: '/parent',
        builder: (_, _) => const ParentHomeScreen(),
        routes: [GoRoute(path: 'children/new', builder: (_, _) => const AddChildScreen())],
      ),
      GoRoute(path: '/student', builder: (_, _) => const StudentHomeScreen()),
    ],
  );
});

class _SplashScreen extends ConsumerWidget {
  const _SplashScreen();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final session = ref.watch(sessionProvider);
    return Scaffold(
      body: Center(
        child: session.hasError
            ? Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Text('Impossible de joindre Mayela. Vérifie ta connexion.', textAlign: TextAlign.center),
                    const SizedBox(height: 16),
                    FilledButton(onPressed: () => ref.invalidate(sessionProvider), child: const Text('Réessayer')),
                  ],
                ),
              )
            : const CircularProgressIndicator(),
      ),
    );
  }
}
