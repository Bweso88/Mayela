import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client.dart';
import '../../auth/data/models.dart';

final catalogRepositoryProvider = Provider<CatalogRepository>(
  (ref) => ApiCatalogRepository(ref.watch(apiClientProvider)),
);

/// Classes proposées dans l'application (Congo pour l'instant), mises en cache pendant la session.
final gradesProvider = FutureProvider<List<GradeRef>>((ref) => ref.watch(catalogRepositoryProvider).grades('CG'));

abstract interface class CatalogRepository {
  Future<List<GradeRef>> grades(String countryCode);

  /// Définit la classe (et la série) d'un enfant ; renvoie le profil à jour.
  Future<StudentProfile> setChildSchooling(String studentId, {required int gradeId, int? trackId});
}

class ApiCatalogRepository implements CatalogRepository {
  ApiCatalogRepository(this._api);

  final ApiClient _api;

  @override
  Future<List<GradeRef>> grades(String countryCode) async {
    final json = await _api.get('/catalog/${countryCode.toLowerCase()}/grades');
    return (json['data'] as List).map((g) => GradeRef.fromJson(g as Map<String, dynamic>)).toList();
  }

  @override
  Future<StudentProfile> setChildSchooling(String studentId, {required int gradeId, int? trackId}) async {
    final json = await _api.put('/parent/children/$studentId/schooling', {'grade_id': gradeId, 'track_id': trackId});
    return StudentProfile.fromJson(json['data'] as Map<String, dynamic>);
  }
}
