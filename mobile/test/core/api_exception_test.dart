import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mayela/core/api/api_exception.dart';

DioException _error(int status, Object? data) {
  final options = RequestOptions(path: '/x');
  return DioException(
    requestOptions: options,
    response: Response(requestOptions: options, statusCode: status, data: data),
  );
}

void main() {
  test('lit les erreurs de validation Laravel (422)', () {
    final e = ApiException.fromDio(
      _error(422, {
        'message': 'Le champ code PIN est obligatoire.',
        'errors': {
          'pin': ['Le champ code PIN est obligatoire.'],
        },
      }),
    );

    expect(e.statusCode, 422);
    expect(e.message, 'Le champ code PIN est obligatoire.');
    expect(e.firstErrorFor('pin'), 'Le champ code PIN est obligatoire.');
    expect(e.firstErrorFor('autre'), isNull);
  });

  test('traduit la limitation de requêtes (429)', () {
    expect(ApiException.fromDio(_error(429, null)).message, contains('Trop de tentatives'));
  });

  test('signale une session expirée (401)', () {
    expect(ApiException.fromDio(_error(401, {'message': 'Unauthenticated.'})).isUnauthenticated, isTrue);
  });

  test('distingue l\'absence de réseau', () {
    final e = ApiException.fromDio(DioException(requestOptions: RequestOptions(path: '/x')));
    expect(e.isNetworkError, isTrue);
    expect(e.message, contains('connexion internet'));
  });
}
